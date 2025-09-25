<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\AcademicClassSctream;
use App\Models\Enterprise;
use App\Models\Participant;
use App\Models\Session;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceDashboardController extends Controller
{
    public function index(Request $request, Content $content)
    {
        $user = Admin::user();
        if (!Admin::user()) {
            return redirect()->route('admin.login');
        }

        $enterprise_id = $user->enterprise_id;
        $ent = Enterprise::find($enterprise_id);
        
        if (!$ent) {
            return redirect()->back()->with('error', 'Enterprise not found');
        }

        // Get current term as default
        $current_term = Term::where('enterprise_id', $enterprise_id)
            ->where('is_active', 1)
            ->orderBy('id', 'desc')
            ->first();
        
        // Get date filters - default to 4 months ago
        $start_date = $request->get('start_date', Carbon::now()->subMonths(4)->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $term_id = $request->get('term_id', $current_term ? $current_term->id : null);
        $class_id = $request->get('class_id', null);
        $attendance_type = $request->get('attendance_type', 'CLASS_ATTENDANCE');

        // Get available terms and classes for filters
        $terms = Term::where('enterprise_id', $enterprise_id)->orderBy('id', 'desc')->get();
        $classes = AcademicClass::where('enterprise_id', $enterprise_id)
            ->whereHas('academic_year', function($q) {
                $q->where('is_active', 1);
            })
            ->orderBy('name')->get();
        $streams = AcademicClassSctream::where('enterprise_id', $enterprise_id)->orderBy('name')->get();
        
        // Attendance/Roll-call types
        $attendance_types = [
            'STUDENT_REPORT' => 'Student Report at School',
            'STUDENT_LEAVE' => 'Student Leave School', 
            'STUDENT_MEAL' => 'Student Meals Session',
            'CLASS_ATTENDANCE' => 'Class attendance',
            'THEOLOGY_ATTENDANCE' => 'Theology Class attendance',
            'ACTIVITY_ATTENDANCE' => 'Activity participation',
        ];

        // Get analytics data
        $overall_stats = $this->getOverallStats($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type);
        $gender_stats = $this->getGenderStats($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type);
        $class_stats = $this->getClassStats($enterprise_id, $start_date, $end_date, $term_id, $attendance_type);
        $stream_stats = $this->getStreamStats($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type);
        $attendance_trend = $this->getAttendanceTrend($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type);
        $top_absent_students = $this->getTopAbsentStudents($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type, 20);
        $top_present_students = $this->getTopPresentStudents($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type, 20);
        $type_stats = $this->getAttendanceTypeStats($enterprise_id, $start_date, $end_date, $term_id, $class_id);
        
        // New analytics
        $secular_theology_stats = $this->getSecularVsTheologyStats($enterprise_id, $start_date, $end_date, $term_id);
        $secular_class_stats = $this->getSecularClassStats($enterprise_id, $start_date, $end_date, $term_id);
        $theology_class_stats = $this->getTheologyClassStats($enterprise_id, $start_date, $end_date, $term_id);

        return $content
            ->title('Comprehensive attendance insights for Kira Junior School - Kito')
            ->description('')
            ->view('admin.attendance-dashboard', compact(
                'ent', 'start_date', 'end_date', 'term_id', 'class_id', 'attendance_type',
                'terms', 'classes', 'streams', 'attendance_types', 'overall_stats', 'gender_stats', 
                'class_stats', 'stream_stats', 'attendance_trend', 'type_stats',
                'top_absent_students', 'top_present_students', 'secular_theology_stats',
                'secular_class_stats', 'theology_class_stats'
            ));
    }

    private function buildWhereClause($enterprise_id, $start_date, $end_date, $term_id = null, $class_id = null, $attendance_type = null)
    {
        $query = Participant::where('participants.enterprise_id', $enterprise_id)
            ->whereBetween(DB::raw('DATE(participants.created_at)'), [$start_date, $end_date])
            ->join('admin_users', 'participants.administrator_id', '=', 'admin_users.id')
            ->where('admin_users.status', 1); // Only active students

        if ($term_id) {
            $query->where('participants.term_id', $term_id);
        }

        if ($class_id) {
            $query->where('participants.academic_class_id', $class_id);
        }

        if ($attendance_type) {
            $query->where('participants.type', $attendance_type);
        }

        return $query;
    }

    private function getOverallStats($enterprise_id, $start_date, $end_date, $term_id = null, $class_id = null, $attendance_type = null)
    {
        $query = $this->buildWhereClause($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type);
        
        $stats = $query->selectRaw('
            COUNT(DISTINCT administrator_id) as unique_students,
            SUM(CASE WHEN is_present = 1 THEN 1 ELSE 0 END) as total_present,
            SUM(CASE WHEN is_present = 0 THEN 1 ELSE 0 END) as total_absent,
            COUNT(*) as total_records
        ')->first();

        // Count actual roll call sessions made
        $total_sessions_query = Session::where('enterprise_id', $enterprise_id)
            ->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date]);
        
        if ($term_id) {
            $total_sessions_query->where('term_id', $term_id);
        }
        
        if ($attendance_type) {
            $total_sessions_query->where('type', $attendance_type);
        }
        
        $stats->total_sessions = $total_sessions_query->count();

        if ($stats->total_records > 0) {
            $stats->attendance_rate = round(($stats->total_present / $stats->total_records) * 100, 1);
        } else {
            $stats->attendance_rate = 0;
        }

        return $stats;
    }

    private function getGenderStats($enterprise_id, $start_date, $end_date, $term_id = null, $class_id = null, $attendance_type = null)
    {
        $results = $this->buildWhereClause($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type)
            ->selectRaw('
                admin_users.sex as gender,
                COUNT(DISTINCT admin_users.id) as student_count,
                SUM(CASE WHEN participants.is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN participants.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            ')
            ->groupBy('admin_users.sex')
            ->get();

        // Calculate total students for percentage calculation
        $total_students = $results->sum('student_count');

        return $results->map(function($item) use ($total_students) {
            $item->attendance_rate = $item->total_records > 0 
                ? round(($item->present_count / $item->total_records) * 100, 1) 
                : 0;
            $item->percentage = $total_students > 0 
                ? round(($item->student_count / $total_students) * 100, 1) 
                : 0;
            return $item;
        });
    }

    private function getClassStats($enterprise_id, $start_date, $end_date, $term_id = null, $attendance_type = null)
    {
        $query = $this->buildWhereClause($enterprise_id, $start_date, $end_date, $term_id, null, $attendance_type);
        
        $results = $query->join('academic_classes as ac', 'participants.academic_class_id', '=', 'ac.id')
            ->join('academic_years as ay', 'ac.academic_year_id', '=', 'ay.id')
            ->where('ay.is_active', 1) // Only current academic year classes
            ->selectRaw('
                ac.id as class_id,
                ac.name as class_name,
                ac.short_name,
                SUM(CASE WHEN participants.is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN participants.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            ')
            ->groupBy('ac.id', 'ac.name', 'ac.short_name')
            ->get();

        // If no data, return at least one default entry to show the table structure
        if ($results->isEmpty()) {
            return collect([(object)[
                'class_id' => null,
                'class_name' => 'No Data Available',
                'short_name' => 'N/A',
                'present_count' => 0,
                'absent_count' => 0,
                'total_records' => 0,
                'attendance_rate' => 0
            ]]);
        }

        return $results->map(function($item) {
            $item->attendance_rate = $item->total_records > 0 
                ? round(($item->present_count / $item->total_records) * 100, 1) 
                : 0;
            return $item;
        });
    }

    private function getStreamStats($enterprise_id, $start_date, $end_date, $term_id = null, $class_id = null, $attendance_type = null)
    {
        // This method would require stream information in participants table or join with student_has_streams
        return collect([]); // Placeholder for now
    }

    private function getAttendanceTrend($enterprise_id, $start_date, $end_date, $term_id = null, $class_id = null, $attendance_type = null)
    {
        return $this->buildWhereClause($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type)
            ->selectRaw('
                DATE(participants.created_at) as date,
                SUM(CASE WHEN is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            ')
            ->groupBy(DB::raw('DATE(participants.created_at)'))
            ->orderBy(DB::raw('DATE(participants.created_at)'))
            ->get()
            ->map(function($item) {
                $item->attendance_rate = $item->total_records > 0 
                    ? round(($item->present_count / $item->total_records) * 100, 1) 
                    : 0;
                return $item;
            });
    }

    private function getTopAbsentStudents($enterprise_id, $start_date, $end_date, $term_id = null, $class_id = null, $attendance_type = null, $limit = 10)
    {
        return $this->buildWhereClause($enterprise_id, $start_date, $end_date, $term_id, $class_id, $attendance_type)
            ->leftJoin('academic_classes as ac', 'admin_users.current_class_id', '=', 'ac.id')
            ->leftJoin('theology_classes as tc', 'admin_users.current_theology_class_id', '=', 'tc.id')
            ->selectRaw('
                admin_users.id,
                admin_users.name as student_name,
                admin_users.avatar,
                ac.name as class_name,
                tc.name as theology_class_name,
                SUM(CASE WHEN participants.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_sessions
            ')
            ->groupBy('admin_users.id', 'admin_users.name', 'admin_users.avatar', 'ac.name', 'tc.name')
            ->orderByDesc('absent_count')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                $item->attendance_rate = $item->total_sessions > 0 
                    ? round((($item->total_sessions - $item->absent_count) / $item->total_sessions) * 100, 1) 
                    : 0;
                
                // Format name with classes in brackets - use short names
                $classes = [];
                if ($item->class_name) {
                    // Extract short name (e.g., "P.1" from "Primary one, P.1 2025")
                    $short_class = $this->extractShortClassName($item->class_name);
                    $classes[] = $short_class;
                }
                if ($item->theology_class_name) {
                    $short_theology = $this->extractShortClassName($item->theology_class_name);
                    $classes[] = $short_theology . ' (Theology)';
                }
                
                $item->display_name = $item->student_name . 
                    (count($classes) > 0 ? ' (' . implode(', ', $classes) . ')' : '');
                
                return $item;
            });
    }

    private function getTopPresentStudents($enterprise_id, $start_date, $end_date, $term_id = null, $class_id = null, $attendance_type = null, $limit = 10)
    {
        // For best attendance, focus on regular classes (not theology)
        $query = $this->buildWhereClause($enterprise_id, $start_date, $end_date, $term_id, $class_id, null);
        
        // Filter to only regular class attendance types
        $regular_types = ['CLASS_ATTENDANCE', 'STUDENT_REPORT', 'STUDENT_LEAVE', 'STUDENT_MEAL', 'ACTIVITY_ATTENDANCE'];
        $query = $query->whereIn('participants.type', $regular_types);
        
        return $query
            ->leftJoin('academic_classes as ac', 'admin_users.current_class_id', '=', 'ac.id')
            ->leftJoin('theology_classes as tc', 'admin_users.current_theology_class_id', '=', 'tc.id')
            ->selectRaw('
                admin_users.id,
                admin_users.name as student_name,
                admin_users.avatar,
                ac.name as class_name,
                tc.name as theology_class_name,
                SUM(CASE WHEN participants.is_present = 1 THEN 1 ELSE 0 END) as present_count,
                COUNT(*) as total_sessions
            ')
            ->groupBy('admin_users.id', 'admin_users.name', 'admin_users.avatar', 'ac.name', 'tc.name')
            ->orderByDesc('present_count')
            ->limit($limit)
            ->get()
            ->map(function($item) {
                $item->attendance_rate = $item->total_sessions > 0 
                    ? round(($item->present_count / $item->total_sessions) * 100, 1) 
                    : 0;
                
                // Format name with classes in brackets - use short names
                $classes = [];
                if ($item->class_name) {
                    // Extract short name (e.g., "P.1" from "Primary one, P.1 2025")
                    $short_class = $this->extractShortClassName($item->class_name);
                    $classes[] = $short_class;
                }
                // Don't show theology class for best attendance (focusing on regular classes only)
                
                $item->display_name = $item->student_name . 
                    (count($classes) > 0 ? ' (' . implode(', ', $classes) . ')' : '');
                
                return $item;
            });
    }

    private function getAttendanceTypeStats($enterprise_id, $start_date, $end_date, $term_id = null, $class_id = null)
    {
        $query = Participant::where('participants.enterprise_id', $enterprise_id)
            ->whereBetween(DB::raw('DATE(participants.created_at)'), [$start_date, $end_date]);

        if ($term_id) {
            $query->where('participants.term_id', $term_id);
        }

        if ($class_id) {
            $query->where('participants.academic_class_id', $class_id);
        }

        return $query->selectRaw('
                participants.type,
                SUM(CASE WHEN is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            ')
            ->groupBy('participants.type')
            ->get()
            ->map(function($item) {
                $item->attendance_rate = $item->total_records > 0 
                    ? round(($item->present_count / $item->total_records) * 100, 1) 
                    : 0;
                
                // Map type codes to readable names
                $type_names = [
                    'STUDENT_REPORT' => 'Student Report at School',
                    'STUDENT_LEAVE' => 'Student Leave School',
                    'STUDENT_MEAL' => 'Student Meals Session',
                    'CLASS_ATTENDANCE' => 'Class attendance',
                    'THEOLOGY_ATTENDANCE' => 'Theology Class attendance',
                    'ACTIVITY_ATTENDANCE' => 'Activity participation',
                ];
                
                $item->type_name = $type_names[$item->type] ?? $item->type;
                return $item;
            });
    }

    private function getSecularVsTheologyStats($enterprise_id, $start_date, $end_date, $term_id = null)
    {
        $secular_types = ['CLASS_ATTENDANCE', 'STUDENT_REPORT', 'STUDENT_LEAVE', 'STUDENT_MEAL', 'ACTIVITY_ATTENDANCE'];
        $theology_types = ['THEOLOGY_ATTENDANCE'];
        
        $secular_stats = Participant::where('participants.enterprise_id', $enterprise_id)
            ->whereBetween(DB::raw('DATE(participants.created_at)'), [$start_date, $end_date])
            ->whereIn('participants.type', $secular_types)
            ->when($term_id, function($query, $term_id) {
                return $query->where('participants.term_id', $term_id);
            })
            ->selectRaw('
                SUM(CASE WHEN is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            ')->first();
            
        $theology_stats = Participant::where('participants.enterprise_id', $enterprise_id)
            ->whereBetween(DB::raw('DATE(participants.created_at)'), [$start_date, $end_date])
            ->whereIn('participants.type', $theology_types)
            ->when($term_id, function($query, $term_id) {
                return $query->where('participants.term_id', $term_id);
            })
            ->selectRaw('
                SUM(CASE WHEN is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            ')->first();
            
        return [
            (object)[
                'type_name' => 'Secular Classes',
                'present_count' => $secular_stats->present_count ?? 0,
                'absent_count' => $secular_stats->absent_count ?? 0,
                'total_records' => $secular_stats->total_records ?? 0,
                'attendance_rate' => $secular_stats->total_records > 0 
                    ? round(($secular_stats->present_count / $secular_stats->total_records) * 100, 1) 
                    : 0
            ],
            (object)[
                'type_name' => 'Theology Classes',
                'present_count' => $theology_stats->present_count ?? 0,
                'absent_count' => $theology_stats->absent_count ?? 0,
                'total_records' => $theology_stats->total_records ?? 0,
                'attendance_rate' => $theology_stats->total_records > 0 
                    ? round(($theology_stats->present_count / $theology_stats->total_records) * 100, 1) 
                    : 0
            ]
        ];
    }

    private function getSecularClassStats($enterprise_id, $start_date, $end_date, $term_id = null)
    {
        $secular_types = ['CLASS_ATTENDANCE', 'STUDENT_REPORT', 'STUDENT_LEAVE', 'STUDENT_MEAL', 'ACTIVITY_ATTENDANCE'];
        
        $query = Participant::where('participants.enterprise_id', $enterprise_id)
            ->whereBetween(DB::raw('DATE(participants.created_at)'), [$start_date, $end_date])
            ->whereIn('participants.type', $secular_types)
            ->join('admin_users', 'participants.administrator_id', '=', 'admin_users.id')
            ->where('admin_users.status', 1);
            
        if ($term_id) {
            $query->where('participants.term_id', $term_id);
        }
        
        return $query->join('academic_classes as ac', 'participants.academic_class_id', '=', 'ac.id')
            ->join('academic_years as ay', 'ac.academic_year_id', '=', 'ay.id')
            ->where('ay.is_active', 1)
            ->selectRaw('
                ac.id as class_id,
                ac.name as class_name,
                SUM(CASE WHEN participants.is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN participants.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            ')
            ->groupBy('ac.id', 'ac.name')
            ->get()
            ->map(function($item) {
                $item->attendance_rate = $item->total_records > 0 
                    ? round(($item->present_count / $item->total_records) * 100, 1) 
                    : 0;
                return $item;
            });
    }

    private function getTheologyClassStats($enterprise_id, $start_date, $end_date, $term_id = null)
    {
        $theology_types = ['THEOLOGY_ATTENDANCE'];
        
        $query = Participant::where('participants.enterprise_id', $enterprise_id)
            ->whereBetween(DB::raw('DATE(participants.created_at)'), [$start_date, $end_date])
            ->whereIn('participants.type', $theology_types)
            ->join('admin_users', 'participants.administrator_id', '=', 'admin_users.id')
            ->where('admin_users.status', 1);
            
        if ($term_id) {
            $query->where('participants.term_id', $term_id);
        }
        
        return $query->join('theology_classes as tc', 'admin_users.current_theology_class_id', '=', 'tc.id')
            ->selectRaw('
                tc.id as class_id,
                tc.name as class_name,
                SUM(CASE WHEN participants.is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN participants.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            ')
            ->groupBy('tc.id', 'tc.name')
            ->get()
            ->map(function($item) {
                $item->attendance_rate = $item->total_records > 0 
                    ? round(($item->present_count / $item->total_records) * 100, 1) 
                    : 0;
                return $item;
            });
    }

    private function extractShortClassName($className)
    {
        if (!$className) return '';
        
        // Handle cases like "Primary one, P.1 2025" -> "P.1"
        // or "Primary two, P.2 2025 (Theology)" -> "P.2"
        if (preg_match('/P\.(\d+)/i', $className, $matches)) {
            return 'P.' . $matches[1];
        }
        
        // Handle cases like "Senior one, S.1 2025" -> "S.1"
        if (preg_match('/S\.(\d+)/i', $className, $matches)) {
            return 'S.' . $matches[1];
        }
        
        // Handle "Primary one" -> "P.1", "Primary two" -> "P.2", etc.
        if (preg_match('/Primary\s+(one|two|three|four|five|six|seven)/i', $className, $matches)) {
            $numbers = [
                'one' => '1', 'two' => '2', 'three' => '3', 'four' => '4',
                'five' => '5', 'six' => '6', 'seven' => '7'
            ];
            $number = $numbers[strtolower($matches[1])] ?? $matches[1];
            return 'P.' . $number;
        }
        
        // Handle "Senior one" -> "S.1", "Senior two" -> "S.2", etc.
        if (preg_match('/Senior\s+(one|two|three|four|five|six)/i', $className, $matches)) {
            $numbers = [
                'one' => '1', 'two' => '2', 'three' => '3', 'four' => '4',
                'five' => '5', 'six' => '6'
            ];
            $number = $numbers[strtolower($matches[1])] ?? $matches[1];
            return 'S.' . $number;
        }
        
        // Handle other patterns or fallback to first word
        $parts = explode(',', $className);
        if (count($parts) > 1) {
            $shortPart = trim($parts[1]);
            // Extract just P.1, S.1 etc. without year
            if (preg_match('/([PS]\.\d+)/', $shortPart, $matches)) {
                return $matches[1];
            }
        }
        
        // Last resort - take first word
        return trim(explode(' ', $className)[0]);
    }

    public function export(Request $request)
    {
        // Export functionality can be implemented here
        return response()->json(['message' => 'Export functionality to be implemented']);
    }
}
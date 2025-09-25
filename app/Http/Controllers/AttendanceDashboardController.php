<?php

namespace App\Http\Controllers;

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
            return redirect('/admin/auth/login');
        }

        $enterprise_id = $user->enterprise_id;
        $ent = Enterprise::find($enterprise_id);
        
        if (!$ent) {
            return redirect()->back()->with('error', 'Enterprise not found');
        }

        // Get date filters
        $start_date = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $end_date = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $term_id = $request->get('term_id', null);
        $class_id = $request->get('class_id', null);

        // Get available terms and classes for filters
        $terms = Term::where('enterprise_id', $enterprise_id)->orderBy('id', 'desc')->get();
        $classes = AcademicClass::where('enterprise_id', $enterprise_id)->orderBy('name')->get();
        $streams = AcademicClassSctream::where('enterprise_id', $enterprise_id)->orderBy('name')->get();

                // Build conditions for filtering
        $conditions = [];
        $params = [$start_date, $end_date];
        $date_condition = "DATE(p.created_at) BETWEEN ? AND ?";

        $conditions['p.enterprise_id'] = $enterprise_id;

        if ($term_id) {
            $conditions['p.term_id'] = $term_id;
        }

        if ($class_id) {
            $conditions['p.academic_class_id'] = $class_id;
        }

        // 1. Gender-based statistics
        $gender_stats = $this->getGenderStats($conditions, $date_condition, $params);

        // 2. Class-based statistics  
        $class_stats = $this->getClassStats($conditions, $date_condition, $params);

        // 3. Stream-based statistics
        $stream_stats = $this->getStreamStats($conditions, $date_condition, $params);

        // 4. Time-based statistics
        $daily_stats = $this->getDailyStats($conditions, $date_condition, $params);
        $monthly_stats = $this->getMonthlyStats($conditions, $params, $start_date, $end_date);
        $term_stats = $this->getTermStats($enterprise_id);
        $yearly_stats = $this->getYearlyStats($enterprise_id);

        // 5. Student rankings
        $top_absent_students = $this->getTopAbsentStudents($conditions, $date_condition, $params, 20);
        $top_present_students = $this->getTopPresentStudents($conditions, $date_condition, $params, 20);
        $perfect_attendance = $this->getPerfectAttendanceStudents($conditions, $date_condition, $params);
        $never_present = $this->getNeverPresentStudents($conditions, $date_condition, $params);
        $high_absence_10 = $this->getHighAbsenceStudents($conditions, $date_condition, $params, 10);
        $high_absence_20 = $this->getHighAbsenceStudents($conditions, $date_condition, $params, 20);

        // 6. Overall totals
        $overall_stats = $this->getOverallStats($conditions, $date_condition, $params);

        // 7. Recent attendance trends
        $attendance_trend = $this->getAttendanceTrend($conditions, $params, $start_date, $end_date);

        return $content->view('admin.attendance-dashboard', compact(
            'gender_stats', 'class_stats', 'stream_stats', 'daily_stats', 
            'monthly_stats', 'term_stats', 'yearly_stats', 'top_absent_students',
            'top_present_students', 'perfect_attendance', 'never_present',
            'high_absence_10', 'high_absence_20', 'overall_stats',
            'attendance_trend', 'terms', 'classes', 'streams',
            'start_date', 'end_date', 'term_id', 'class_id', 'ent'
        ));
    }

    private function getGenderStats($conditions, $date_condition, $params)
    {
        $enterprise_id = $conditions['p.enterprise_id'] ?? null;
        
        $query = DB::table('participants as p')
            ->join('admin_users as u', 'p.administrator_id', '=', 'u.id')
            ->select(
                'u.sex',
                DB::raw('SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('COUNT(*) as total_records')
            )
            ->where('u.user_type', 'student');
            
        if ($enterprise_id) {
            $query->where('p.enterprise_id', $enterprise_id);
        }
        
        if (isset($params[0]) && isset($params[1])) {
            $query->whereBetween(DB::raw('DATE(p.created_at)'), [$params[0], $params[1]]);
        }
        
        return $query->groupBy('u.sex')
                    ->orderBy('u.sex')
                    ->get()
                    ->toArray();
    }

    private function getClassStats($conditions, $date_condition, $params)
    {
        $enterprise_id = $conditions['p.enterprise_id'] ?? null;
        
        $query = DB::table('participants as p')
            ->join('admin_users as u', 'p.administrator_id', '=', 'u.id')
            ->leftJoin('academic_classes as ac', 'u.current_class_id', '=', 'ac.id')
            ->select(
                'ac.name as class_name',
                'ac.id as class_id',
                DB::raw('SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('COUNT(*) as total_records'),
                DB::raw('ROUND((SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate')
            )
            ->where('u.user_type', 'student');
            
        if ($enterprise_id) {
            $query->where('p.enterprise_id', $enterprise_id);
        }
        
        if (isset($params[0]) && isset($params[1])) {
            $query->whereBetween(DB::raw('DATE(p.created_at)'), [$params[0], $params[1]]);
        }
        
        return $query->groupBy('ac.id', 'ac.name')
                    ->orderByDesc('attendance_rate')
                    ->get()
                    ->toArray();
    }

    private function getStreamStats($conditions, $date_condition, $params)
    {
        $enterprise_id = $conditions['p.enterprise_id'] ?? null;
        
        $query = DB::table('participants as p')
            ->join('admin_users as u', 'p.administrator_id', '=', 'u.id')
            ->leftJoin('academic_class_sctreams as s', 'u.stream_id', '=', 's.id')
            ->select(
                's.name as stream_name',
                's.id as stream_id',
                DB::raw('SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('COUNT(*) as total_records'),
                DB::raw('ROUND((SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate')
            )
            ->where('u.user_type', 'student')
            ->whereNotNull('u.stream_id');
            
        if ($enterprise_id) {
            $query->where('p.enterprise_id', $enterprise_id);
        }
        
        if (isset($params[0]) && isset($params[1])) {
            $query->whereBetween(DB::raw('DATE(p.created_at)'), [$params[0], $params[1]]);
        }
        
        return $query->groupBy('s.id', 's.name')
                    ->orderByDesc('attendance_rate')
                    ->get()
                    ->toArray();
    }

    private function getDailyStats($conditions, $date_condition, $params)
    {
        $enterprise_id = $conditions['p.enterprise_id'] ?? null;
        
        $query = DB::table('participants as p')
            ->join('admin_users as u', 'p.administrator_id', '=', 'u.id')
            ->select(
                DB::raw('DATE(p.created_at) as attendance_date'),
                DB::raw('SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('COUNT(*) as total_records')
            )
            ->where('u.user_type', 'student');
            
        if ($enterprise_id) {
            $query->where('p.enterprise_id', $enterprise_id);
        }
        
        if (isset($params[0]) && isset($params[1])) {
            $query->whereBetween(DB::raw('DATE(p.created_at)'), [$params[0], $params[1]]);
        }
        
        return $query->groupBy(DB::raw('DATE(p.created_at)'))
                    ->orderByDesc(DB::raw('DATE(p.created_at)'))
                    ->limit(30)
                    ->get()
                    ->toArray();
    }

    private function getMonthlyStats($conditions, $params, $start_date, $end_date)
    {
        $enterprise_id = $conditions['p.enterprise_id'] ?? null;
        
        $query = DB::table('participants as p')
            ->join('admin_users as u', 'p.administrator_id', '=', 'u.id')
            ->select(
                DB::raw('YEAR(p.created_at) as year'),
                DB::raw('MONTH(p.created_at) as month'),
                DB::raw('MONTHNAME(p.created_at) as month_name'),
                DB::raw('SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count'),
                DB::raw('COUNT(*) as total_records')
            )
            ->where('u.user_type', 'student');
            
        if ($enterprise_id) {
            $query->where('p.enterprise_id', $enterprise_id);
        }
        
        return $query->groupBy(DB::raw('YEAR(p.created_at)'), DB::raw('MONTH(p.created_at)'), DB::raw('MONTHNAME(p.created_at)'))
                    ->orderByDesc(DB::raw('YEAR(p.created_at)'))
                    ->orderByDesc(DB::raw('MONTH(p.created_at)'))
                    ->limit(12)
                    ->get()
                    ->toArray();
    }

    private function getTermStats($enterprise_id)
    {
        $sql = "
            SELECT 
                t.name as term_name,
                t.id as term_id,
                SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            FROM participants p
            INNER JOIN admin_users u ON p.administrator_id = u.id
            INNER JOIN terms t ON p.term_id = t.id
            WHERE p.enterprise_id = ? AND u.user_type = 'student'
            GROUP BY t.id, t.name
            ORDER BY t.id DESC
        ";

        return DB::select($sql, [$enterprise_id]);
    }

    private function getYearlyStats($enterprise_id)
    {
        $sql = "
            SELECT 
                YEAR(p.created_at) as year,
                SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_records
            FROM participants p
            INNER JOIN admin_users u ON p.administrator_id = u.id
            WHERE p.enterprise_id = ? AND u.user_type = 'student'
            GROUP BY YEAR(p.created_at)
            ORDER BY YEAR(p.created_at) DESC
        ";

        return DB::select($sql, [$enterprise_id]);
    }

    private function getTopAbsentStudents($conditions, $date_condition, $params, $limit)
    {
        $whereClause = $this->buildWhereClause($conditions);
        
        $sql = "
            SELECT 
                u.name as student_name,
                u.id as student_id,
                u.avatar,
                ac.name as class_name,
                SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present_count,
                COUNT(*) as total_sessions,
                ROUND((SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as absence_rate
            FROM participants p
            INNER JOIN admin_users u ON p.administrator_id = u.id
            LEFT JOIN academic_classes ac ON u.current_class_id = ac.id
            WHERE {$whereClause} AND {$date_condition} AND u.user_type = 'student'
            GROUP BY u.id, u.name, ac.name
            HAVING absent_count > 0
            ORDER BY absent_count DESC, absence_rate DESC
            LIMIT {$limit}
        ";

        return DB::select($sql, $params);
    }

    private function getTopPresentStudents($conditions, $date_condition, $params, $limit)
    {
        $whereClause = $this->buildWhereClause($conditions);
        
        $sql = "
            SELECT 
                u.name as student_name,
                u.id as student_id,
                u.avatar,
                ac.name as class_name,
                SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_sessions,
                ROUND((SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate
            FROM participants p
            INNER JOIN admin_users u ON p.administrator_id = u.id
            LEFT JOIN academic_classes ac ON u.current_class_id = ac.id
            WHERE {$whereClause} AND {$date_condition} AND u.user_type = 'student'
            GROUP BY u.id, u.name, ac.name
            HAVING present_count > 0
            ORDER BY present_count DESC, attendance_rate DESC
            LIMIT {$limit}
        ";

        return DB::select($sql, $params);
    }

    private function getPerfectAttendanceStudents($conditions, $date_condition, $params)
    {
        $whereClause = $this->buildWhereClause($conditions);
        
        $sql = "
            SELECT 
                u.name as student_name,
                u.id as student_id,
                u.avatar,
                ac.name as class_name,
                COUNT(*) as total_sessions
            FROM participants p
            INNER JOIN admin_users u ON p.administrator_id = u.id
            LEFT JOIN academic_classes ac ON u.current_class_id = ac.id
            WHERE {$whereClause} AND {$date_condition} AND u.user_type = 'student'
            GROUP BY u.id, u.name, ac.name
            HAVING COUNT(*) = SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) AND COUNT(*) > 0
            ORDER BY total_sessions DESC, u.name
        ";

        return DB::select($sql, $params);
    }

    private function getNeverPresentStudents($conditions, $date_condition, $params)
    {
        $whereClause = $this->buildWhereClause($conditions);
        
        $sql = "
            SELECT 
                u.name as student_name,
                u.id as student_id,
                u.avatar,
                ac.name as class_name,
                COUNT(*) as total_sessions
            FROM participants p
            INNER JOIN admin_users u ON p.administrator_id = u.id
            LEFT JOIN academic_classes ac ON u.current_class_id = ac.id
            WHERE {$whereClause} AND {$date_condition} AND u.user_type = 'student'
            GROUP BY u.id, u.name, ac.name
            HAVING SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) = 0 AND COUNT(*) > 0
            ORDER BY total_sessions DESC, u.name
        ";

        return DB::select($sql, $params);
    }

    private function getHighAbsenceStudents($conditions, $date_condition, $params, $min_absences)
    {
        $whereClause = $this->buildWhereClause($conditions);
        
        $sql = "
            SELECT 
                u.name as student_name,
                u.id as student_id,
                u.avatar,
                ac.name as class_name,
                SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent_count,
                COUNT(*) as total_sessions,
                ROUND((SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as absence_rate
            FROM participants p
            INNER JOIN admin_users u ON p.administrator_id = u.id
            LEFT JOIN academic_classes ac ON u.current_class_id = ac.id
            WHERE {$whereClause} AND {$date_condition} AND u.user_type = 'student'
            GROUP BY u.id, u.name, ac.name
            HAVING absent_count >= {$min_absences}
            ORDER BY absent_count DESC, absence_rate DESC
        ";

        return DB::select($sql, $params);
    }

    private function getOverallStats($conditions, $date_condition, $params)
    {
        $enterprise_id = $conditions['p.enterprise_id'] ?? null;
        
        $query = DB::table('participants as p')
            ->join('admin_users as u', 'p.administrator_id', '=', 'u.id')
            ->select(
                DB::raw('SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as total_present'),
                DB::raw('SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as total_absent'),
                DB::raw('COUNT(*) as total_records'),
                DB::raw('COUNT(DISTINCT u.id) as unique_students'),
                DB::raw('COUNT(DISTINCT DATE(p.created_at)) as days_with_records'),
                DB::raw('ROUND((SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as overall_attendance_rate')
            )
            ->where('u.user_type', 'student');
            
        if ($enterprise_id) {
            $query->where('p.enterprise_id', $enterprise_id);
        }
        
        if (isset($params[0]) && isset($params[1])) {
            $query->whereBetween(DB::raw('DATE(p.created_at)'), [$params[0], $params[1]]);
        }

        $result = $query->first();
        return $result ? (array) $result : null;
    }

    private function getAttendanceTrend($conditions, $params, $start_date, $end_date)
    {
        $whereClause = $this->buildWhereClause($conditions);
        
        $sql = "
            SELECT 
                DATE(p.created_at) as date,
                SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN p.is_present = 0 THEN 1 ELSE 0 END) as absent,
                COUNT(*) as total,
                ROUND((SUM(CASE WHEN p.is_present = 1 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as attendance_rate
            FROM participants p
            INNER JOIN admin_users u ON p.administrator_id = u.id
            WHERE {$whereClause} AND DATE(p.created_at) BETWEEN ? AND ? AND u.user_type = 'student'
            GROUP BY DATE(p.created_at)
            ORDER BY DATE(p.created_at) ASC
        ";

        return DB::select($sql, array_merge(array_slice($params, 2), [$start_date, $end_date]));
    }

    private function buildWhereClause($conditions)
    {
        $clauses = [];
        foreach ($conditions as $field => $value) {
            $clauses[] = "{$field} = '{$value}'";
        }
        return implode(' AND ', $clauses);
    }

    public function export(Request $request)
    {
        // Export functionality can be added here
        // Similar to print but with Excel/CSV export
        return response()->json(['message' => 'Export functionality coming soon']);
    }
}
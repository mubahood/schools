<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;


class Gen extends Model
{
  use HasFactory;



  public static function to_json($recs)
  {
    $_data = "";
    foreach ($recs as $v) {
      $key = trim($v);
      if (strlen($key) < 2) {
        continue;
      }
      $_data .= "'$key' : $key,<br>";
    }

    return $_data;
  }

  public static function fromJsons($recs = [])
  {
    $_data = "";
    foreach ($recs as $v) {
      $key = trim($v);
      if (strlen($key) < 1) {
        continue;
      }
      if ($key == 'id') {
        $_data .= "obj.{$key} = Utils.int_parse(m['{$key}']);<br>";
      } else {
        $_data .= "obj.{$key} = Utils.to_str(m['{$key}'],'');<br>";
      }
    }
    return $_data;
  }

  public  function makeVars($tables)
  {


    $_data = "";
    $i = 0;
    $done = [];
    foreach ($tables as $v) {
      $key = trim($v);
      if (strlen($key) < 1) {
        continue;
      }
      if (in_array($key, $done)) {
        continue;
      }
      $done[] = $key;
      if ($key == 'id') {
        $_data .= "int {$key} = 0;<br>";
      } else {
        $_data .= "String {$key} = \"\";<br>";
        if (str_contains($key, '_id')) {
          $_key = str_replace('_id', '_text', $key);
          $_data .= "String {$_key} = \"\";<br>";
        }
      }
    }

    return $_data;
  }


  public  function sqlTableVars($tables)
  {


    $_data = "";
    $i = 0;
    $isFirst = true;
    $done = [];
    foreach ($tables as $v) {
      $key = trim($v);
      if (strlen($key) < 1) {
        continue;
      }
      if (in_array($key, $done)) {
        continue;
      }
      $done[] = $key;
      if ($key == 'id') {
        $_data .= "\"{$key} INTEGER PRIMARY KEY\"<br>";
      } else {
        $_data .= "\",{$key} TEXT\"<br>";
        if (str_contains($key, '_id')) {
          $_key = str_replace('_id', '_text', $key);
          $_data .= "\",{$_key} TEXT\"<br>";
        }
      }
    }

    return $_data;
  }


  public function do_get()
  {
    $tables = Schema::getColumnListing($this->table_name);
    $generate_vars = $this->makeVars($tables);
    $fromJson = Gen::fromJsons($tables);
    $toJson = Gen::to_json($tables);
    $sqlTableVars = Gen::sqlTableVars($tables);
    $x = <<<EOT
  <pre>import 'package:schooldynamics/utils/Utils.dart';
  import 'package:sqflite/sqflite.dart';
   
  import 'RespondModel.dart';
 
  class $this->class_name {
     
    static String endPoint = "{$this->end_point}";
    static String tableName = "{$this->end_point}";
    $generate_vars
  
    static fromJson(dynamic m) {
    $this->class_name obj = new $this->class_name();
      if (m == null) {
        return obj;
      }
      
    $fromJson
    return obj;
  }
  
    
  
  
    static Future&lt;List&lt;$this->class_name&gt;&gt; getLocalData({String where: "1"}) async {

      List&lt$this->class_name&gt data = [];
      if (!(await $this->class_name.initTable())) {
        Utils.toast("Failed to init dynamic store.");
        return data;
      }
  
      Database db = await Utils.getDb();
      if (!db.isOpen) {
        return data;
      }
  
  
      List&ltMap&gt maps = await db.query(tableName, where: where);
  
      if (maps.isEmpty) {
        return data;
      }
      List.generate(maps.length, (i) {
        data.add($this->class_name.fromJson(maps[i]));
      });
  
      return data;
      
    }
  
  
    static Future&lt;List&lt;$this->class_name&gt;&gt; getItems({String where = '1'}) async {
      List&lt;$this->class_name&gt; data = await getLocalData(where: where);
      if (data.isEmpty) {
        await $this->class_name.getOnlineItems();
        data = await getLocalData(where: where);
      } else {
        data = await getLocalData(where: where);
        $this->class_name.getOnlineItems();
      }
      data.sort((a, b) => b.id.compareTo(a.id));
      return data;
    }
  
    static Future&lt;List&lt;$this->class_name&gt;&gt; getOnlineItems() async {
      List&lt;$this->class_name&gt; data = [];

      RespondModel resp =
          RespondModel(await Utils.http_get('\${{$this->class_name}.endPoint}', {}));
   
      if (resp.code != 1) {
        return [];
      }
  
      Database db = await Utils.getDb();
      if (!db.isOpen) {
        Utils.toast("Failed to init local store.");
        return [];
      }
  
      if (resp.data.runtimeType.toString().contains('List')) {
        if (await Utils.is_connected()) {
          await {$this->class_name}.deleteAll();
        }
  
        await db.transaction((txn) async {
          var batch = txn.batch();
  
          for (var x in resp.data) {
            {$this->class_name} sub = {$this->class_name}.fromJson(x);
            try {
              batch.insert(tableName, sub.toJson(),
                  conflictAlgorithm: ConflictAlgorithm.replace);
            } catch (e) {
              print("faied to save becaus \${e.toString()}");
            }
          }
  
          try {
            await batch.commit(continueOnError: true);
          } catch (e) {
            print("faied to save to commit BRECASE ==> \${e.toString()}");
          }
        });
      }
   
  
      return data; 
    }
   
    save() async {
      Database db = await Utils.getDb();
      if (!db.isOpen) {
        Utils.toast("Failed to init local store.");
        return;
      }
  
      await initTable();
  
      try {
        await db.insert(
          tableName,
          toJson(),
          conflictAlgorithm: ConflictAlgorithm.replace,
        );
      } catch (e) {
        Utils.toast("Failed to save student because \${e.toString()}");
      }
    }
  
    toJson() {
      return {
       $toJson
      };
    }
  

    

    
  static Future<bool> initTable() async {
    Database db = await Utils.getDb();
    if (!db.isOpen) {
      return false;
    }

    String sql = " CREATE TABLE IF NOT EXISTS "
        "{$this->table_name} ("
        $sqlTableVars
        ")";

    try {
      //await db.delete(tableName);

      await db.execute("DROP TABLE \${tableName}");
    } catch (e) {
      Utils.log('Failed to create table because \${e . toString()}');

      return false;
    }

    return true;
  }

 
  static deleteAll() async {
    if (!(await {$this->class_name}.initTable())) {
      return;
    }
    Database db = await Utils.getDb();
    if (!db.isOpen) {
      return false;
    }
    await db.delete({$this->class_name}.tableName);
  }

  }
  </pre>
  EOT;

    return  $x;
  }
}

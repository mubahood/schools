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
      if (str_contains($key, '_id')) {
        $_key = str_replace('_id', '_text', $key);
        $_data .= "'$_key' : $_key,<br>";
      }
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
        if (str_contains($key, '_id')) {
          $_key = str_replace('_id', '_text', $key);
          $_data .= "obj.{$_key} = Utils.to_str(m['{$_key}'],'');<br>";
        }
      }
    }


    return $_data;
  }

  public  function makeFormFields($tables)
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
      } else {

        $_key = str_replace('_id', '_text', $key);

        $_data .= 'FormBuilderTextField(
          decoration: CustomTheme.in_3(
            label: GetStringUtils("' . (str_replace('_', ' ', $_key)) . '").capitalize!,
          ),
          initialValue: item.' . $_key . ',
          textCapitalization: TextCapitalization.words,
          name: "' . $_key . '",
          onChanged: (x) {
            item.' . $_key . ' = x.toString();
          },
          textInputAction: TextInputAction.next,
        ),const SizedBox(height: 15),';
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
  <pre>import 'package:sqflite/sqflite.dart';
 
  import '../utils/Utils.dart';
  import 'RespondModel.dart';
 
  class $this->class_name {
      
    static String end_point = "{$this->end_point}";
    static String tableName = "{$this->table_name}";
    $generate_vars
  
    static fromJson(dynamic m) {
    $this->class_name obj = new $this->class_name();
      if (m == null) {
        return obj;
      }
      
    $fromJson
    return obj;
  }
  
    
  
  
    static Future&lt;List&lt;$this->class_name&gt;&gt; getLocalData({String where = "1"}) async {

      List&lt$this->class_name&gt data = [];
      if (!(await $this->class_name.initTable())) {
        Utils.toast("Failed to init dynamic store.");
        return data;
      }
  
      Database db = await Utils.getDb();
      if (!db.isOpen) {
        return data;
      }
  
  
      List&ltMap&gt maps = await db.query(tableName, where: where, orderBy: ' id DESC ');
  
      if (maps.isEmpty) {
        return data;
      }
      List.generate(maps.length, (i) {
        data.add($this->class_name.fromJson(maps[i]));
      });
  
      return data;
      
    }
  
  
    static Future&lt;List&lt;$this->class_name&gt;&gt; get_items({String where = '1'}) async {
      List&lt;$this->class_name&gt; data = await getLocalData(where: where);
      if (data.isEmpty ) {
        await $this->class_name.getOnlineItems();
        data = await getLocalData(where: where);
      }else{
        $this->class_name.getOnlineItems();
      }
      return data;
    }
  
    static Future&lt;List&lt;$this->class_name&gt;&gt; getOnlineItems() async {
      List&lt;$this->class_name&gt; data = [];

      RespondModel resp =
          RespondModel(await Utils.http_get('\${{$this->class_name}.end_point}', {}));
   
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
            print("faied to save to commit BRECASE == \${e.toString()}");
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
        "\$tableName ("
        $sqlTableVars
        ")";

    try {
      //await db.execute("DROP TABLE \${tableName}");
      await db.execute(sql);
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




  
  delete() async {
    Database db = await Utils.getDb();
    if (!db.isOpen) {
      Utils.toast("Failed to init local store.");
      return;
    }

    await initTable();

    try {
      await db.delete(
        tableName,
        where: 'id = \$id'
      );
    } catch (e) {
      Utils.toast("Failed to save student because \${e.toString()}");
    }
  }
  

  }
  </pre>
  EOT;

    return  $x;
  }









  public function make_forms()
  {
    $tables = Schema::getColumnListing($this->table_name);
    $forrm_fields = $this->makeFormFields($tables);
    $generate_vars = $this->makeVars($tables);
    $fromJson = Gen::fromJsons($tables);
    $toJson = Gen::to_json($tables);
    $sqlTableVars = Gen::sqlTableVars($tables);
    $x = <<<EOT
  <pre>
  import 'package:flutter/material.dart';
  import 'package:flutter_feather_icons/flutter_feather_icons.dart';
  import 'package:flutter_form_builder/flutter_form_builder.dart';
  import 'package:flutx/flutx.dart';
  import 'package:get/get.dart'; 
  
  import '../../models/$this->class_name.dart';
  import '../../models/RespondModel.dart';
  import '../../utils/CustomTheme.dart';
  import '../../utils/Utilities.dart';
  
  class {$this->class_name}EditScreen extends StatefulWidget {
    Map&ltString, dynamic&gt params = {};
  
    {$this->class_name}EditScreen(
      this.params, {
      Key? key,
    }) : super(key: key);
  
    @override
    {$this->class_name}EditScreenState createState() =&gt {$this->class_name}EditScreenState();
  }
  
  class {$this->class_name}EditScreenState extends State&lt{$this->class_name}EditScreen&gt
      with SingleTickerProviderStateMixin {
    var initFuture;
    final _fKey = GlobalKey&ltFormBuilderState&gt();
    bool is_loading = false;
    String error_message = "";
    {$this->class_name} item = {$this->class_name}();
  
    Future&ltbool&gt init_form() async {
      if (widget.params['item'].runtimeType == item.runtimeType) {
        item = widget.params['item'];
      }
      return true;
    }
  
    @override
    void initState() {
      super.initState();
      initFuture = init_form();
    }
  
    @override
    Widget build(BuildContext context) {
      _keyboardVisible = MediaQuery.of(context).viewInsets.bottom != 0;
      return Scaffold(
        appBar: AppBar(
          title: FxText.titleMedium(
            "Editing profile",
            fontSize: 20,
            fontWeight: 700,
          ),
          iconTheme: const IconThemeData(color: Colors.white),
          backgroundColor: CustomTheme.primary,
          actions: [
            is_loading
                ? const Padding(
                    padding: EdgeInsets.only(right: 20, top: 10, bottom: 10),
                    child: Center(
                      child: CircularProgressIndicator(
                        strokeWidth: 2.0,
                        valueColor:
                            AlwaysStoppedAnimation&ltColor&gt(CustomTheme.primary),
                      ),
                    ),
            )
                : FxButton.text(
                    onPressed: () {
                      submit_form();
                    },
                    backgroundColor: Colors.white,
                    child: FxText.bodyLarge(
                      "SAVE",
                      fontWeight: 800,
                    ))
          ],
        ),
        body: FutureBuilder(
            future: initFuture,
            builder: (context, snapshot) {
              if (!snapshot.hasData) {
                return const Center(child: CircularProgressIndicator());
              }
  
              return FormBuilder(
                key: _fKey,
                child: Column(
                  children: [
                    Expanded(
                      child: SingleChildScrollView(
                        child: Container(
                          padding: const EdgeInsets.only(
                            left: 15,
                            top: 10,
                            right: 15,
                          ),
                          child: Column(
                            children: [
                              const SizedBox(height: 10),
                              error_message.isEmpty
                                  ? const SizedBox()
                                  : FxContainer(
                                      margin: const EdgeInsets.only(bottom: 10),
                                      color: Colors.red.shade50,
                                      child: Text(
                                        error_message,
                                      ),
                                    ),
                              {$forrm_fields}
                              
                            ],
                          ),
                        ),
                      ),
                    ),
                    _keyboardVisible
                        ? SizedBox()
                        : FxContainer(
                            color: Colors.white,
                            borderRadiusAll: 0,
                            padding: const EdgeInsets.symmetric(
                                vertical: 10, horizontal: 15),
                            child: FxButton.block(
                                onPressed: () {
                                  submit_form();
                                },
                                backgroundColor: CustomTheme.primary,
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    FxText.titleLarge(
                                      'NEXT',
                                      color: Colors.white,
                                    ),
                                    const SizedBox(
                                      width: 10,
                                    ),
                                    const Icon(
                                      FeatherIcons.arrowRight,
                                      color: Colors.white,
                                    )
                                  ],
                                )))
                  ],
                ),
              );
            }),
      );
    }
  
    bool _keyboardVisible = false;
  
    submit_form() async {
      if (!_fKey.currentState!.validate()) {
        Utils.toast('Fix some errors first.', color: Colors.red.shade700);
        return;
      }
      setState(() {
        error_message = "";
        is_loading = true;
      });
  
      Utils.toast('Updating...', color: Colors.green.shade700);
  
      RespondModel resp = RespondModel(
          await Utils.http_post({$this->class_name}.end_point, item.toJson()));
  
      setState(() {
        error_message = "";
        is_loading = false;
      });
  
      if (resp.code != 1) {
        is_loading = false;
        error_message = resp.message;
        setState(() {});
        Utils.toast('Failed', color: Colors.red.shade700);
        return;
      }
  
      Utils.toast('Successfully!');
  
      Navigator.pop(context);
      return;
    }
  }  
  </pre>
  EOT;

    return  $x;
  }
}

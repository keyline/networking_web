<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BulkImportJob extends Model {protected $guarded=[];protected $casts=['headers'=>'array','mapping'=>'array','options'=>'array','errors'=>'array','completed_at'=>'datetime'];}

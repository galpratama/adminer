<?php

/** Convention foreign keys: plugin for Adminer
* Links for foreign keys by convention user_id => users.id. Useful for Ruby On Rails like standard schema conventions.
* @author Ivan Nečas, @inecas
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/
class ConventionForeignKeys {

        function foreignKeys($table) {
          $ret = array();
          foreach(fields($table) as $field => $args){
            if(ereg("^(.*)_id$", $field, $args)){
              $ret[] = array("table" => $args[1]."s", "source" => array($field), "target" => array("id"));
            }
          }
          return $ret;
        }
}
?>

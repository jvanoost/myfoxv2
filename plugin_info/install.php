<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';


function myfoxv2_install() {
	 $cron = cron::byClassAndFunction('myfoxv2', 'maj');
  if (is_object($cron)) {
      $cron->remove();
  }
}

function myfoxv2_update() {
	 $cron = cron::byClassAndFunction('myfoxv2', 'maj');
  if (is_object($cron)) {
      $cron->remove();
	   
  }
    $cronP = cron::byClassAndFunction('myfoxv2', 'pull');
	  if (!is_object($cronP)) {
				$cronP = new cron();
				$cronP->setClass('myfoxv2');
				$cronP->setFunction('pull');
				$cronP->setOption(array('myfoxv2_id' => intval($this->getId())));
				$cronP->setLastRun(date('Y-m-d H:i:s'));
				$cronP->setEnable(1);
				$cronP->setDeamon(1);
				$cronP->setTimeout('30');
				$cronP->setSchedule('* * * * *');
				$cronP->save();
				log::add('myfoxv2', 'debug', 'addCron');
		  
		  
	  }
}

function myfoxv2_remove() {
	
    $cron = cron::byClassAndFunction('myfoxv2', 'maj');
    if (is_object($cron)) {
        $cron->remove();
    }
	 $cron = cron::byClassAndFunction('myfoxv2', 'pull');
    if (is_object($cron)) {
        $cron->remove();
    }
}
}
?>

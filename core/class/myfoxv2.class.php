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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

define('MYFOXURL', 'https://api.myfox.me:443/v2');
define('TOKEN_ENDPOINT','https://api.myfox.me/oauth2/token');
//include_file('core', 'myfoxv2', 'config' , 'myfoxv2');

class myfoxv2 extends eqLogic {
	
	public static function deamon_info() {
		$return = array();
		$return['log'] = '';
		$return['state'] = 'nok';
		$cron = cron::byClassAndFunction('myfoxv2', 'pull');
		if (is_object($cron) && $cron->running()) {
			$return['state'] = 'ok';
		}
		$return['launchable'] = 'ok';
		return $return;
	}

	public static function deamon_start($_debug = false) {
		//maj des crons suite a mise a jour plugin en deamon
		
		 $cron = cron::byClassAndFunction('myfoxv2', 'maj');
			if (is_object($cron)) {
				$cron->remove();
			}
		$cronP = cron::byClassAndFunction('myfoxv2', 'pull');
		  if (!is_object($cronP)) {
					$cronP = new cron();
					$cronP->setClass('myfoxv2');
					$cronP->setFunction('pull');
					//$cronP->setOption(array('myfoxv2_id' => intval($this->getId())));
					$cronP->setLastRun(date('Y-m-d H:i:s'));
					$cronP->setEnable(1);
					$cronP->setDeamon(1);
					$cronP->setTimeout('30');
					$cronP->setSchedule('* * * * *');
					$cronP->save();
					log::add('myfoxv2', 'debug', 'addCron');
		  }

		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$cron = cron::byClassAndFunction('myfoxv2', 'pull');
		if (!is_object($cron)) {
			throw new Exception(__('Tache cron introuvable', __FILE__));
		}
		$cron->run();
    
	}

	public static function deamon_stop() {
		$cron = cron::byClassAndFunction('myfoxv2', 'pull');
		if (!is_object($cron)) {
			throw new Exception(__('Tache cron introuvable', __FILE__));
		}
		$cron->halt();
	}

	public static function pull() {
	
	
		foreach (eqLogic::byType('myfoxv2') as $eqLogic){
if (is_object($eqLogic) && $eqLogic->getIsEnable() == 1) {		
			
				foreach($eqLogic->getCmd('info') as $Commande){
				$Commande->execute();}
					
		}				
      }
	  }

	
	public function checkCredential() {
				$token=$this->getConfiguration('myfoxv2Token');
				$tokenExpire=$this->getConfiguration('myfoxv2TokenExpire');
				
		if (($token=='') ||  (time() > $tokenExpire)){ 
		
				$password=$this->getConfiguration('myfoxv2Password');
                $username=$this->getConfiguration('myfoxv2Username');
                $client_id=$this->getConfiguration('myfoxv2ClientId');
                $client_secret=$this->getConfiguration('myfoxv2ClientSecret');
		
				
		
		$curl = curl_init( TOKEN_ENDPOINT );
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, array(
    				'grant_type' => 'password',
			        'client_id' => $client_id,
			        'client_secret' => $client_secret,
			        'username' => $username,
		    		'password' => $password
		) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
		$auth = curl_exec( $curl );
	
		
		
		log::add('myfoxv2', 'debug','token credential : ' .$auth); 
			
			return $auth;
			
		}
			
	}
	

	public function getToken() {
	  //$this = $this->getEqLogic();
				$token=$this->getConfiguration('myfoxv2Token');
				$tokenExpire=$this->getConfiguration('myfoxv2TokenExpire');
				$tokenRefresh=$this->getConfiguration('myfoxv2TokenRefresh');
                $password=$this->getConfiguration('myfoxv2Password');
                $username=$this->getConfiguration('myfoxv2Username');
                $client_id=$this->getConfiguration('myfoxv2ClientId');
                $client_secret=$this->getConfiguration('myfoxv2ClientSecret');
	
				
			if(($token!=='') AND  (time() < $tokenExpire)){ 
			//log::add('Myfox','debug', 'tokenexiste not expired  '. $token);
			
			  return $token;
			  
			  //Refresh token expiré
			  } else if(($token!=='') AND  (time() > $tokenExpire)){
			// Authentification
		$curl = curl_init( TOKEN_ENDPOINT );
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, array(
    				'grant_type' => 'refresh_token',
					'refresh_token' => $tokenRefresh,
			        'client_id' => $client_id,
			        'client_secret' => $client_secret,
					
		) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
		//log::add('Myfox', 'curl', $curl);
		$auth = curl_exec( $curl );
		$secret = json_decode($auth);
		$err = $secret->error;
		if ($err) { 
		     
			log::add('myfoxv2', 'error','erreur myfoxv2 : ' .$auth); 
			$this->setConfiguration('myfoxv2Token','');
			$this->setConfiguration('myfoxv2TokenExpire','');
			$this->setConfiguration('myfoxv2TokenRefresh','');
			$this->save();
			return $err;
		}
		else {
		    $token = $secret->access_token;
			$tokenexpire = $secret->expires_in;
			$tokenrefresh = $secret->refresh_token;
			//log::add('myfoxv2','debug', 'secret2 token  ' .$token);
			
			$this->setConfiguration('myfoxv2Token',$token);
			$this->setConfiguration('myfoxv2TokenExpire',time()+($tokenexpire-300));
			$this->setConfiguration('myfoxv2TokenRefresh',$tokenrefresh);
			$this->save();
		}
		//log::add('Myfox','debug', 'tokenrefreshnew  '. $token);
		return $token;
		
	    } else if($token==''){
		$curl = curl_init( TOKEN_ENDPOINT );
		curl_setopt( $curl, CURLOPT_POST, true );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, array(
    				'grant_type' => 'password',
			        'client_id' => $client_id,
			        'client_secret' => $client_secret,
			        'username' => $username,
		    		'password' => $password
		) );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1);
		$auth = curl_exec( $curl );
		$secret = json_decode($auth);
		$err = $secret->error;
		if ($err) { 
		    
			log::add('myfoxv2', 'error','erreur myfoxv22 : ' .$auth); 
			$this->setConfiguration('myfoxv2Token','');
			$this->setConfiguration('myfoxv2TokenExpire','');
			$this->setConfiguration('myfoxv2TokenRefresh','');
			$this->save();
			
			return $err;
		}
		else {
		    $token = $secret->access_token;
			$tokenexpire = $secret->expires_in;
			$tokenrefresh = $secret->refresh_token;
			log::add('myfoxv2', 'debug', "tokencreate ".$token);
			
			$this->setConfiguration('myfoxv2Token',$token);
			$this->setConfiguration('myfoxv2TokenExpire',time()+($tokenexpire-300));
			$this->setConfiguration('myfoxv2TokenRefresh',$tokenrefresh);
			$this->save();
		}
		//log::add('Myfox', 'tokennew', $token);
		return $token;
		}
	
	}
	
	public function eraseConfToken() {
		$erase = myfoxv2::byId($this->getId());
		$erase->setConfiguration('myfoxv2Token','');
		$erase->setConfiguration('myfoxv2TokenExpire','');
		$erase->setConfiguration('myfoxv2TokenRefresh','');
		$erase->save();
			
		
	}
	
	public function getSiteId($token,$force=0) {
	//$eqLogic_myfoxv2 = $this->getEqLogic();
	if ($this->getConfiguration('siteId')!=='' && $force !=1) {
	//log::add('myfoxv2', 'debug', 'force ' .$force);
	   return $this->getConfiguration('siteId');
	} else {
		log::add('myfoxv2', 'debug', 'siteIdReload');
		
		$api_url = MYFOXURL . "/client/site/items?access_token=" . $token;
		$requete = @file_get_contents($api_url);
		$json_result = json_decode($requete,true);
		
		//Boucle sur retour json
		
				foreach ($json_result["payload"]["items"][0] as $key => $v1) {
							log::add('myfoxv2', 'debug','equipements : ' .$key.'    '.$v1); 
									$this->setConfiguration($key,$v1);
								
																	
				}
				
			$this->save(true);
		
		
		return $json_result["payload"]["items"][0]["siteId"];
		}
	}
	
	public function getDeviceType($type) {
		
		$api_url = MYFOXURL . "/site/".$this->getConfiguration('siteId')."/device/".$type."/items?access_token=" . $this->getConfiguration('myfoxv2Token');
		//log::add('Myfox', 'debug', 'api url get device type'.$api_url);
		$requete1 = @file_get_contents($api_url);
		$json_result = json_decode($requete1,true);
		
		
				if($json_result["status"]=='OK') {
					$return = $json_result["payload"]["items"];
						} else {  $return = 'error';  }
						
			return $return;
	}
	
	public function getSite($type) {
		
		$api_url = MYFOXURL . "/site/".$this->getConfiguration('siteId')."/".$type."/items?access_token=" . $this->getConfiguration('myfoxv2Token');
		//log::add('Myfox', 'debug', 'api url get device type'.$api_url);
		$requete1 = @file_get_contents($api_url);
		$json_result = json_decode($requete1,true);
		
		
				if($json_result["status"]=='OK') {
					$return = $json_result["payload"]["items"];
						} else {  $return = 'error';  }
						
			return $return;
	}
	
	public function createDevice($type,$value) {
	
		if($value > '0' ) {
			$myfoxData=myfoxv2::getDeviceType($type);
			if($myfoxData!=='error') {
			
		//Boucle sur retour json
				foreach ($myfoxData as $key => $v1) {	

						$OpenClose = 'open';
						$initial = array("open","close");
						$replace = array("ON","OFF");
						$replaceSocket = array("on","off");
									
									
						if($type=='shutter' || $type=='socket') {
									
									for ($i = 1; $i <= 2; $i++) {
										
										
						$logicID=str_replace(' ','',$v1["label"].$i);
									
								$aCmd = $this->getCmd(null, $logicID);
								if (!is_object($aCmd)) {
								$aCmd = new myfoxv2Cmd();
								$aCmd->setLogicalId($logicID);
								$aCmd->setIsVisible(0);
								$aCmd->setName(__($v1["label"].' '.str_replace($initial,$replace,$OpenClose), __FILE__));
									}
									
								if($type=='socket') {
								$aCmd->setConfiguration('request', '/site/#siteId#/device/'.$v1["deviceId"].'/'.$type.'/'.str_replace($initial,$replaceSocket,$OpenClose));
								if($OpenClose=='open') {
								$aCmd->setDisplay('generic_type','LIGHT_ON');
														} else { $aCmd->setDisplay('generic_type','LIGHT_OFF');}
													} else {
								$aCmd->setConfiguration('request', '/site/#siteId#/device/'.$v1["deviceId"].'/'.$type.'/'.$OpenClose);
												if($OpenClose=='open') {
								$aCmd->setDisplay('generic_type','FLAP_UP');
																		} else { 
																		$aCmd->setDisplay('generic_type','FLAP_DOWN');}				
												
								}
								$aCmd->setType('action');
								$aCmd->setSubType('other');
								$aCmd->setEqLogic_id($this->getId());
								$aCmd->save();
								$OpenClose = 'close';
																}
													} else {	$oneTwo = 'one';
																$initial = array("one","two");
																$replace = array("1","2");
																for ($i = 1; $i <= 2; $i++) {
													
																$logicID=str_replace(' ','',$v1["label"].$i);
									
																$aCmd = $this->getCmd(null, $logicID);
																if (!is_object($aCmd)) {
																$aCmd = new myfoxv2Cmd();
																$aCmd->setLogicalId($logicID);
																$aCmd->setIsVisible(0);
																$aCmd->setName(__($v1["label"].' '.str_replace($initial,$replace,$oneTwo), __FILE__));
																	}
																$aCmd->setConfiguration('request', '/site/#siteId#/device/'.$v1["deviceId"].'/'.$type.'/perform/'.$oneTwo);
																$aCmd->setType('action');
																$aCmd->setDisplay('generic_type','GENERIC_ACTION');		
																$aCmd->setSubType('other');
																$aCmd->setEqLogic_id($this->getId());
																$aCmd->save();
																$oneTwo = 'two';
																							}
															}
					}
				}
		}
		
	 }


	 public function preRemove()
    {/*
        //$cron = cron::byClassAndFunction('myfoxv2', 'pull', array('myfoxv2_id' => intval($this->getId())));
		$cron = cron::byClassAndFunction('myfoxv2', 'pull');
        if (is_object($cron)) {
            $cron->stop();
            $cron->remove();
			log::add('myfoxv2', 'debug', 'stopRemoveCron');
        }*/
    }
	

    public function preUpdate() {
	
        if ($this->getConfiguration('myfoxv2ClientSecret') == '') {
            throw new Exception(__('Le Client Secret ne peut être vide', __FILE__));
        }

        if ($this->getConfiguration('myfoxv2ClientId') == '') {
            throw new Exception(__('Le Client ID ne peut être vide', __FILE__));
        }

        if ($this->getConfiguration('myfoxv2Username') == '') {
            throw new Exception(__('Le Username ne peut être vide', __FILE__));
        }

        if ($this->getConfiguration('myfoxv2Password') == '') {
            throw new Exception(__('Le password ne peut être vide', __FILE__));
        }
		
		
    }
	
   public function postSave() {
	  

  } 

	public function postUpdate() {
		
			$myfoxErrorToken=myfoxv2::checkCredential();
			//$cron = cron::byClassAndFunction('myfoxv2', 'pull',array('myfoxv2_id' => intval($this->getId())));
			$cron = cron::byClassAndFunction('myfoxv2', 'pull');
			
			
	
		log::add('myfoxv2', 'debug', 'PostUpdate : '.$myfoxErrorToken);
		if(preg_match("/error|blacklisted|KO|login|password/i", $myfoxErrorToken)) {
			
			 if (is_object($cron)) {
            $cron->stop();
            $cron->remove();
			log::add('myfoxv2', 'debug', 'stopRemoveCron');
        }
			throw new Exception(__($myfoxErrorToken, __FILE__));
			
		} 
		
		//on recheck les elements myfox 
		$token = myfoxv2::getToken();	
		$siteID = myfoxv2::getSiteId($token,true);
		log::add('myfoxv2', 'debug', 'siteId&Token : '.$token . ' ' .$siteID);
			
		
		
		if ($this->getConfiguration('deviceTemperatureCount') > '0') {
		$myfoxData=myfoxv2::getDeviceType('data/temperature');
		
		foreach ($myfoxData as $key => $v1) {
			
		$temperature = $this->getCmd(null, 'temperature'.$v1["deviceId"]);
		if (!is_object($temperature)) {
			$temperature = new myfoxv2Cmd();
			$temperature->setLogicalId('temperature'.$v1["deviceId"]);
			$temperature->setIsVisible(1);
			$temperature->setName(__('Temperature '.$v1["label"], __FILE__));
		}
		$temperature->setConfiguration('request', '/site/#siteId#/device/data/temperature/items');
		$temperature->setConfiguration('response', 'lastTemperature');
		$temperature->setConfiguration('deviceId', $v1["deviceId"]);
		$temperature->setType('info');
		$temperature->setUnite('°C');
		$temperature->setSubType('numeric');
		$temperature->setEventOnly(1);
		$temperature->setIsHistorized(1);
		$temperature->setDisplay('generic_type','TEMPERATURE');
		$temperature->setTemplate('dashboard','tile');
		$temperature->setTemplate('mobile','tile');
		$temperature->setConfiguration('onlyChangeEvent',1);
		$temperature->setEqLogic_id($this->getId());
		$temperature->save();
		//log::add('Myfox', 'debug', "save");
		
		}
		}
		
		if ($this->getConfiguration('deviceLightCount') > '0') {
		$myfoxData=myfoxv2::getDeviceType('data/light');
		
		foreach ($myfoxData as $key => $v1) {
		
		$luminosite = $this->getCmd(null, 'luminosite'.$v1["deviceId"]);
		if (!is_object($luminosite)) {
			$luminosite = new myfoxv2Cmd();
			$luminosite->setLogicalId('luminosite'.$v1["deviceId"]);
			$luminosite->setIsVisible(1);
			$luminosite->setName(__('Luminosite '.$v1["label"], __FILE__));
		}
		$luminosite->setConfiguration('request', '/site/#siteId#/device/data/light/items');
		$luminosite->setConfiguration('response', 'light');
		$luminosite->setConfiguration('deviceId', $v1["deviceId"]);
		$luminosite->setEventOnly(1);
		$luminosite->setConfiguration('onlyChangeEvent',1);
		$luminosite->setType('info');
		$luminosite->setUnite('');
		$luminosite->setSubType('numeric');
		$luminosite->setIsHistorized(1);
		$luminosite->setDisplay('generic_type','BRIGHTNESS');
		$luminosite->setTemplate('dashboard','tile');
		$luminosite->setTemplate('mobile','tile');
		$luminosite->setEqLogic_id($this->getId());
		$luminosite->save();
		
		}
		}
		
		if ($this->getConfiguration('deviceDetectorCount') > '0') {
		$myfoxData=myfoxv2::getDeviceType('data/other');
		
		foreach ($myfoxData as $key => $v1) {
		
		$other = $this->getCmd(null, 'other'.$v1["deviceId"]);
		if (!is_object($other)) {
			$other = new myfoxv2Cmd();
			$other->setLogicalId('other'.$v1["deviceId"]);
			$other->setIsVisible(1);
			$other->setName(__($v1["label"].' '.$v1["modelLabel"], __FILE__));
		}
		$other->setConfiguration('request', '/site/#siteId#/device/data/other/items');
		$other->setConfiguration('response', 'state');
		$other->setConfiguration('deviceId', $v1["deviceId"]);
		$other->setEventOnly(1);
		$other->setConfiguration('onlyChangeEvent',1);
		$other->setType('info');
		$other->setUnite('');
		$other->setSubType('binary');
		$other->setIsHistorized(1);
		$other->setTemplate('dashboard','tile');
		$other->setTemplate('mobile','tile');
		$other->setEqLogic_id($this->getId());
		$other->save();
		
		}
		}
		
		if ($this->getConfiguration('heaterCount') > '0') {
		$myfoxData=myfoxv2::getDeviceType('heater');
		
		foreach ($myfoxData as $key => $v1) {
		
		$heater = $this->getCmd(null, 'heater'.$v1["deviceId"]);
		if (!is_object($heater)) {
			$heater = new myfoxv2Cmd();
			$heater->setLogicalId('heater'.$v1["deviceId"]);
			$heater->setIsVisible(1);
			$heater->setName(__($v1["label"].' '.$v1["modelLabel"], __FILE__));
		}
		$heater->setConfiguration('request', '/site/#siteId#/device/heater/items');
		$heater->setConfiguration('response', 'stateLabel');
		$heater->setConfiguration('deviceId', $v1["deviceId"]);
		$heater->setEventOnly(1);
		$heater->setConfiguration('onlyChangeEvent',1);
		$heater->setType('info');
		$heater->setUnite('');
		$heater->setSubType('string');
		$heater->setIsHistorized(0);
		$heater->setTemplate('dashboard','tile');
		$heater->setTemplate('mobile','tile');
		$heater->setEqLogic_id($this->getId());
		$heater->save();
		
		}
		}
		
		
	
		$state = $this->getCmd(null, 'etat');
		if (!is_object($state)) {
			$state = new myfoxv2Cmd();
			$state->setLogicalId('etat');
			$state->setIsVisible(1);
			$state->setName(__('Etat', __FILE__));
		}
		$state->setConfiguration('request', '/site/#siteId#/security');
		$state->setConfiguration('response', 'statusLabel');
		$state->setEventOnly(1);
		$state->setConfiguration('onlyChangeEvent',1);
		$state->setType('info');
		$state->setSubType('string');
		$state->setIsHistorized(1);
		$state->setDisplay('generic_type','ALARM_MODE');
		$state->setTemplate('dashboard','tile');
		$state->setTemplate('mobile','tile');
		$state->setEqLogic_id($this->getId());
		$state->save();
		
		
		$alarm = $this->getCmd(null, 'alarme');
		if (!is_object($alarm)) {
			$alarm = new myfoxv2Cmd();
			$alarm->setLogicalId('alarme');
			$alarm->setIsVisible(1);
			$alarm->setName(__('Evenement alarme', __FILE__));
		}
		$alarm->setConfiguration('request', '/site/#siteId#/history');
		$alarm->setConfiguration('response', 'label');
		$alarm->setEventOnly(1);
		$alarm->setConfiguration('onlyChangeEvent',1);
		$alarm->setType('info');
		$alarm->setSubType('string');
		$alarm->setDisplay('generic_type','ALARM_STATE');
		$alarm->setEqLogic_id($this->getId());
		$alarm->save();
		
		
		$aTotal = $this->getCmd(null, 'total');
		if (!is_object($aTotal)) {
			$aTotal = new myfoxv2Cmd();
			$aTotal->setLogicalId('total');
			$aTotal->setIsVisible(1);
			$aTotal->setName(__('Armement Total', __FILE__));
			
		}
		$aTotal->setConfiguration('request', '/site/#siteId#/security/set/armed');
		$aTotal->setType('action');
		$aTotal->setSubType('other');
		$aTotal->setDisplay('icon','<i class="icon jeedom-lock-ferme"></i>');
		$aTotal->setDisplay('generic_type','ALARM_SET_MODE');
		$aTotal->setEqLogic_id($this->getId());
		$aTotal->save();
		
		$aPartiel = $this->getCmd(null, 'partiel');
		if (!is_object($aPartiel)) {
			$aPartiel = new myfoxv2Cmd();
			$aPartiel->setLogicalId('partiel');
			$aPartiel->setIsVisible(1);
			$aPartiel->setName(__('Armement Partiel', __FILE__));
		}
		$aPartiel->setConfiguration('request', '/site/#siteId#/security/set/partial');
		$aPartiel->setType('action');
		$aPartiel->setSubType('other');
		$aPartiel->setDisplay('icon','<i class="icon securite-key1"></i>');
		$aPartiel->setDisplay('generic_type','ALARM_SET_MODE');
		$aPartiel->setEqLogic_id($this->getId());
		$aPartiel->save();
		
		$aDesarme = $this->getCmd(null, 'desarmer');
		if (!is_object($aDesarme)) {
			$aDesarme = new myfoxv2Cmd();
			$aDesarme->setLogicalId('desarmer');
			$aDesarme->setIsVisible(1);
			$aDesarme->setName(__('Desarmer', __FILE__));
		}
		$aDesarme->setConfiguration('request', '/site/#siteId#/security/set/disarmed');
		$aDesarme->setType('action');
		$aDesarme->setSubType('other');
		$aDesarme->setDisplay('icon','<i class="icon jeedom-lock-ouvert"></i>');
		$aDesarme->setDisplay('generic_type','ALARM_RELEASED');
		$aDesarme->setEqLogic_id($this->getId());
		$aDesarme->save();
		
		if ($this->getConfiguration('scenarioCount') > '0') {
			$myfoxData=myfoxv2::getSite('scenario');
		
		foreach ($myfoxData as $key => $v1) {
			if($v1["typeLabel"]=="onDemand") {
		
		$scenario = $this->getCmd(null, 'scenario'.$v1["scenarioId"]);
		if (!is_object($scenario)) {
			$scenario = new myfoxv2Cmd();
			$scenario->setLogicalId('scenario'.$v1["scenarioId"]);
			$scenario->setIsVisible(1);
			$scenario->setName(__('Scenario ' .$v1["label"], __FILE__));
		}
		$scenario->setConfiguration('request', '/site/#siteId#/scenario/'.$v1["scenarioId"].'/play');
		
		$scenario->setConfiguration('scenarioId', $v1["scenarioId"]);
		$scenario->setType('action');
		$scenario->setSubType('other');
		$scenario->setDisplay('icon','<i class="fa fa-play-circle-o"></i>');
		$scenario->setEqLogic_id($this->getId());
		$scenario->save();
			}
		}
		}
		
		
		
	 
		$allDevices = array(
		'shutter' => $this->getConfiguration('shutterCount'),
		'socket' =>  $this->getConfiguration('socketCount') ,
		'gate' =>    $this->getConfiguration('gateCount'),
		'module' =>  $this->getConfiguration('moduleCount'));
	 
	 foreach ($allDevices as $key => $v1) {
			log::add('myfoxv2', 'debug', 'alldevice '.$key .'=>'. $v1);
			$creation = myfoxv2::createDevice($key,$v1);
			
		}
			
			if (!is_object($cron)) {
				$cron = new cron();
				$cron->setClass('myfoxv2');
				$cron->setFunction('pull');
				$cron->setOption(array('myfoxv2_id' => intval($this->getId())));
				$cron->setLastRun(date('Y-m-d H:i:s'));
				$cron->setEnable(1);
				$cron->setDeamon(1);
				$cron->setTimeout('30');
				log::add('myfoxv2', 'debug', 'addCron');
			}

			$cron->setSchedule('* * * * *');
			$cron->save();
		
		
		
}

	public function preSave() {
				
		
	}



}

class myfoxv2Cmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */
	
	//convertir anglais vers FR
	public function convertLang($_etat) {
	
		switch ($_etat) {
				case 'armed':
					return 'Armement Total';
				case 'partial':
					return 'Armement Partiel';
				case 'disarmed':
					return 'Desarmé';
					}
	}



    public function preSave() {
		if ($this->getType() == 'action' && $this->getConfiguration('request') == '') {
			throw new Exception(__('La requete ne peut etre vide',__FILE__));
		}
    } 
	
	
	
	
	
	

    public function execute($_options = null) {
	
			$cron = cron::byClassAndFunction('myfoxv2', 'pull');
        if (is_object($cron) && $cron->getEnable()) {
				
				
		//log::add('myfoxv2', 'debug', 'tokenEndpointExecute : '.TOKEN_ENDPOINT);
		log::add('myfoxv2', 'debug', 'request : '.$this->getConfiguration('request'));
		
	        $eqLogic_myfoxv2 = $this->getEqLogic();
		if (is_object($eqLogic_myfoxv2)) {
                $password=$eqLogic_myfoxv2->getConfiguration('myfoxv2Password');
				//log::add('Myfox', 'pass', $password);
                $username=$eqLogic_myfoxv2->getConfiguration('myfoxv2Username');
				//log::add('Myfox', 'user', $username);
                $client_id=$eqLogic_myfoxv2->getConfiguration('myfoxv2ClientId');
				//log::add('Myfox', 'client', $client_id);
                $client_secret=$eqLogic_myfoxv2->getConfiguration('myfoxv2ClientSecret');
				//log::add('Myfox', 'secret', $client_secret);
		
		

		//get SiteId
		
		$token = $eqLogic_myfoxv2->getToken();
		$siteid = $eqLogic_myfoxv2->getSiteId($token);
	
		
		
		$pattern='#siteId#';
		$request=$this->getConfiguration('request');
		$response=$this->getConfiguration('response');
		$deviceId=$this->getConfiguration('deviceId');
		//$getCapabilities = $eqLogic_myfoxv2->getCache($this->getLogicalId());
		//$getCapabilitiesExpriy = $eqLogic_myfoxv2->getCache('expiry'.$this->getLogicalId());
		
		//log::add('Myfox', 'debug', 'idcapteurtemp : '.$idCapteurTemp);
		
		
		
        ///////////////ACTION A FAIRE EN FONCTION du getType() (demande de retour d'info)
		if ($this->getType() == 'info') {
		//Si LES DONNEES N'ONT PAS EXPIRE
		  if ($eqLogic_myfoxv2->getCache($request)!=='' && time() < $eqLogic_myfoxv2->getCache('expiry'.$request)) {
			log::add('myfoxv2', 'debug', 'donnees OK');
			log::add('myfoxv2','debug','cache'.$eqLogic_myfoxv2->getCache($request));
			$json_result = $eqLogic_myfoxv2->getCache($request);
		
		} else {
		
		//Sinon APPEL VERS L'API
		if($this->getLogicalId()=='alarme') {
				$api_url = MYFOXURL . str_replace($pattern, $siteid, $request) ."?access_token=" . $token .'&type=alarm&dateOrder=-1';
				
				
			} else {
			$api_url = MYFOXURL . str_replace($pattern, $siteid, $request) ."?access_token=" . $token;
			}
			
		$requete1 = @file_get_contents($api_url);
		log::add('myfoxv2', 'debug', 'api call : '.$api_url);
		$json_result = json_decode($requete1,true);
		
		//on renregistre en BDD le retour API pour garder en memoire pdt 20 secondes le temps du traitement des autres valeurs
		$eqLogic_myfoxv2->setCache($request,$json_result);
		$eqLogic_myfoxv2->setCache('expiry'.$request,time()+20);
		$eqLogic_myfoxv2->save(true);
	
		}
	
		if(isset($json_result)){
				
				if ($this->getLogicalId()=='luminosite'.$deviceId || 
					$this->getLogicalId()=='temperature'.$deviceId || 
					$this->getLogicalId()=='other'.$deviceId || 
					$this->getLogicalId()=='heater'.$deviceId) {
					
					
						foreach ($json_result["payload"]["items"] as $key => $v1) {
						if($v1["deviceId"] == $deviceId) {
							$return = $v1[$response]; 
						}
						}
				
				}
			
		
			/////////EVENEMENTS DE TYPE ALARM
			if($this->getLogicalId()=='alarme') {
			$return = $json_result["payload"]["items"][0][$response];
		
			if(!empty($return)) {
				$return = $return. ' le ' .date('d-m-Y \à H:i:s', strtotime($json_result["payload"]["items"][0]["createdAt"]));
				 //Indique un message dans le message center
				 if ($return != $this->execCmd($return,2) && $return !=='Aucun') {
				 log::add('myfoxv2', 'error', 'Myfox : '.$return);
				 }
			
			} else { $return ='Aucun';} ;
			
	
		
			
			}
			/////////ETAT
			if($this->getLogicalId()=='etat') $return = self::convertLang($json_result["payload"]["statusLabel"]);
	
			
			if (($return != $this->execCmd($return,2)) && $return !=='' && $return !=='Aucun') {
				
				$this->setCollectDate(date('Y-m-d H:i:s'));
				
			 $this->event($return);
			$this->getEqLogic()->refreshWidget();
			 } 
			 
			return $return; 
		}  
		
				}   else if($this->getType() == 'action') {
		///////////////ACTION A FAIRE EN FONCTION du getType() (demande d'action)
		//CHANGER ETAT DE LALARME
			     $api_url = MYFOXURL . str_replace($pattern, $siteid, $request) ."?access_token=" . $token;
				
				log::add('myfoxv2', 'debug', 'action performed : '. $api_url);
                $curl3 = curl_init( $api_url );
                curl_setopt( $curl3, CURLOPT_POST, true );
                curl_setopt( $curl3, CURLOPT_RETURNTRANSFER, 1);
                $return = curl_exec( $curl3 );
				$pull = myfoxv2::pull();
				
		
					
	}

	}	
}
	}


}

?>

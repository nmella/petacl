<?php	




class Thirdlevel_Pluggto_Model_Call {
		


		
		// get access token
		public function Autenticate($force=false) {
		
	
			$api = Mage::getModel('pluggto/api')->load(1);
		
			// recupera o refresh token do banco
			
			$expire = (int) $api->getExpire();
			$accesstoken = $api->getAccesstoken();
			
			
			if($expire != null && $expire > time() && $accesstoken != null && $accesstoken != '' && $force == false){
				return $accesstoken;
			}
		
			try{
				
			$body = array(
				'grant_type' => 'password',
				'client_id' => trim(Mage::getStoreConfig('pluggto/configuration/client_id')),
				'client_secret' => trim(Mage::getStoreConfig('pluggto/configuration/client_secret')),
				'username' => trim(Mage::getStoreConfig('pluggto/configuration/app_id')),
				'password' => trim(Mage::getStoreConfig('pluggto/configuration/app_secret')),
			);

					
								try{
								$result = $this->doCall('oauth/token',$body,'field','POST');

								// set the access token
								// guarta o hoarario, prazo de expiracao e returna o access token
								if($result['code'] == 200 && $result['success']){
                                    $apis = Mage::getModel('pluggto/api')->load(1);
                                    $expires = time() +  $result['Body']['expires_in'] - 60;
                                    $apis->setExpire($expires);
                                    $apis->setAccesstoken($result['Body']['access_token']);
                                    $apis->setRefreshtoken($result['Body']['refresh_token']);
                                    $apis->save();
                                    return $result['Body']['access_token'];
                                } else {
                                    return false;
                                }
								

				
					} catch (Exception $e){
								throw new Exception($e);
					}
					
					
			} catch (Exception $e){
								throw new Exception($e);
			}
		
		} // end function
		
		public function doCall($model,$body=null,$type=null,$method,$private=true){
                    
                    // buld the post data follwing the api needs

                    $url = 'https://api.plugg.to/';

                    if($type == 'json'){
                    $posts = json_encode($body);
					$header = array('Content-Type:application/json','Accept:application/json');
                    } else if ($type == 'field') {
                   	$posts = http_build_query($body);
					$header = array('Content-Type:application/x-www-form-urlencoded');
                    } else {
                    $header = array('Content-Type:application/json','Accept:application/json');
                   	$posts = $body;
                    }
                    
					if($model != 'oauth/token' && $private == true){
                        $accessToken = $this->Autenticate();
                        if($accessToken){
						$model = $model . '?' . 'access_token=' . $accessToken;
                        } else {
                        $failReturn['Body'] = 'Authentication Fail, was not possible to authenticate to Plugg.To';
                        $failReturn['code'] = 500;
                        return $failReturn;
                        }
                        if($type == 'field'){
                            $model = $model . '&' . $posts;
                        }
					} else {
						$model = $model . '?' . $posts;
					}

					$options = array(
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_HTTPHEADER  => $header,
					CURLOPT_SSL_VERIFYPEER  => false,
					CURLOPT_URL  => $url . $model,
					CURLOPT_POSTFIELDS  => $posts,
					CURLOPT_CUSTOMREQUEST  => $method,
					CURLOPT_CONNECTTIMEOUT  => 10,
					CURLOPT_TIMEOUT  => 60
					);



					$call = curl_init();

		            curl_setopt_array($call,$options);
		            // execute the curl call
		            $dados = curl_exec($call);
		            // get the curl statys
		            $info = curl_getinfo($call);
		            // close the call
		            curl_close($call);
                    $status = true;

                        // check for curl error



						if($dados === false)
						{
                            $status = false;
                            Mage::helper('pluggto')->WriteLogForModule('Error','URL: '.$model.' chamada: '.print_r($options,1).' retorno: '.print_r($dados,1));
						}

                        if($info['http_code'] != 200 && $info['http_code'] != 201){

                            Mage::helper('pluggto')->WriteLogForModule('Error','URL: '.$model.' chamada: '.print_r($options,1).' retorno: '.print_r($dados,1));
                        }

                        $toReturn['Body'] = json_decode($dados,true);
                        $toReturn['code'] = $info['http_code'];
                        $toReturn['success'] = $status;
		                return $toReturn;
		          //      }
						



        } // function end
    } // class end
?>
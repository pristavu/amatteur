<?php

class Apiv1Controller extends JO_Action
{

    private $error = false;

    public function indexAction()
    {
        $this->forward('error', 'error404');
    }

    //===== HELP AS =====//

    public function isLoged()
    {
        $request = $this->getRequest();

        $validate = new Helper_Validate();
        $validate->_set_rules($request->getPost('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
        $validate->_set_rules($request->getPost('password'), $this->translate('Password'), 'not_empty;min_length[4];max_length[30]');

        if ($validate->_valid_form())
        {
            if (is_array($user_data = Model_Users::checkLogin($request->getPost('email'), $request->getPost('password'))))
            {
                JO_Session::set(array('user' => $user_data));
                return $user_data;
            } else
            {
                $this->error = $this->translate('E-mail address and password do not match');
                return false;
            }
        } else
        {
            $this->error = $this->translate('E-mail address and password do not match');
            return false;
        }
    }

    //====== login ====//
    public function loginAction()
    {
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();


        $result = Model_Users::checkLogin($request->getRequest('email'), $request->getRequest('password'));
        if ($result)
        {
            if ($result['status'])
            {
                @setcookie('csrftoken_', md5($result['user_id'] . $request->getDomain() . $result['date_added']), (time() + ((86400 * 366) * 5)), '/', '.' . $request->getDomain());
                JO_Session::set(array('user' => $result));

                //$token = md5(uniqid(rand(), true));
                $token = md5($result['user_id']);

                $_SESSION['token'] = $token;
                JO_Session::set('token', $token);

                $return = array('id' => $result['user_id'],
                    'username' => $result['username'],
                    'token' => $token,
                    'firstname' => $result['firstname'],
                    'lastname' => $result['lastname']); // $user_data;  
            } else
            {
                $return = array('error' => 2, 'description' => $this->translate('This profile is not active.'));
            }
        } else
        {
            $return = array('error' => 1, 'description' => $this->translate('E-mail address and password do not match'));
        }
        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json; charset=utf-8');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== register ====//
    public function registerAction()
    {
        //echo"entrando";
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();

        /*
          error_log("token " . $_POST['token']);
          error_log("user " . md5($_POST['user_id']));

          if (isset($_POST['token']) && $_POST['token'] == md5($_POST['user_id']))
          {
          $_SESSION['token'] = $_POST['token'];


          error_log("antes estoy logado ");


          if (JO_Session::get('user[user_id]'))
          {
          error_log("estoy logado ");
          //$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
          }

          error_log("desupes estoy logado ");
         */
        $shared_content = Model_Users::checkSharedContent($request->getParam('key'), $request->getParam('user_id'));

        if (!JO_Registry::get('enable_free_registration'))
        {
            if (!$shared_content)
            {
                //$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=landing' ) );
            }
        } else
        {
            /*
              $this->view->fb_register = null;
              $fb_ses = JO_Registry::get('facebookapi');
              $session = $fb_ses->getUser();
              if( JO_Registry::get('oauth_fb_key') && JO_Registry::get('oauth_fb_secret') ) {
              $this->view->fb_register = $this->facebook->getLoginUrl(array(
              'redirect_uri' => WM_Router::create( $request->getBaseUrl() . '?controller=facebook&action=login' ),
              'req_perms' => 'email,user_birthday,status_update,user_videos,user_status,user_photos,offline_access,read_friendlists'
              ));
              }
             * 
             */
        }

        if (JO_Registry::get('oauth_in_key'))
        {
            $this->view->instagram_register = WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=instagram&action=register&next=' . urlencode(WM_Router::create($request->getBaseUrl() . '?controller=instagram&action=register')));
        }

        $this->view->error = false;
        if ($request->isPost())
        {

            $validate = new Helper_Validate();
            $validate->_set_rules($request->getRequest('username'), $this->translate('Username'), 'not_empty;min_length[3];max_length[100];username');
            $validate->_set_rules($request->getRequest('firstname'), $this->translate('First name'), 'not_empty;min_length[3];max_length[100]');
            $validate->_set_rules($request->getRequest('lastname'), $this->translate('Last name'), 'not_empty;min_length[3];max_length[100]');
            $validate->_set_rules($request->getRequest('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
            $validate->_set_rules($request->getRequest('password'), $this->translate('Password'), 'not_empty;min_length[4];max_length[30]');
            $validate->_set_rules($request->getRequest('password2'), $this->translate('Confirm password'), 'not_empty;min_length[4];max_length[30]');

            if ($validate->_valid_form())
            {
                if (md5($request->getRequest('password')) != md5($request->getRequest('password2')))
                {
                    $validate->_set_form_errors($this->translate('Password and Confirm Password should be the same'));
                    $validate->_set_valid_form(false);
                }
                if (Model_Users::isExistEmail($request->getRequest('email')))
                {
                    $validate->_set_form_errors($this->translate('This e-mail address is already used'));
                    $validate->_set_valid_form(false);
                }
                if (Model_Users::isExistUsername($request->getRequest('username')))
                {
                    $validate->_set_form_errors($this->translate('This username is already used'));
                    $validate->_set_valid_form(false);
                }
            }

            if ($validate->_valid_form())
            {
                $reg_key = sha1($request->getRequest('email') . $request->getRequest('username'));

                $result = Model_Users::create(array(
                            'username' => $request->getRequest('username'),
                            'firstname' => $request->getRequest('firstname'),
                            'lastname' => $request->getRequest('lastname'),
                            'email' => $request->getRequest('email'),
                            'password' => $request->getRequest('password'),
                            'delete_email' => isset($shared_content['email']) ? $shared_content['email'] : '',
                            'delete_code' => isset($shared_content['if_id']) ? $shared_content['if_id'] : '',
                            'following_user' => isset($shared_content['user_id']) ? $shared_content['user_id'] : '',
                            'facebook_id' => isset($shared_content['facebook_id']) ? $shared_content['facebook_id'] : 0,
                            'confirmed' => '0',
                            'regkey' => $reg_key
                        ));

                if ($result)
                {
                    if (self::sendMail($result))
                    {
                        //self::loginInit($result);
                    };
                    $return = array('id' => $result); //['user_id']); 
                } else
                {
                    $return = array('error' => 3, 'description' => $this->translate('There was a problem with the record. Please try again!'));
                }
            } else
            {
                $return = array('error' => 4, 'description' => $validate->_get_error_messages());
            }
        }


        $this->view->baseUrl = $request->getBaseUrl();

        if ($request->issetPost('email'))
        {
            $this->view->email = $request->getRequest('email');
        } else
        {
            if (isset($shared_content['email']))
            {
                $this->view->email = $shared_content['email'];
            } else
            {
                $this->view->email = '';
            }
        }

        if ($request->issetPost('firstname'))
        {
            $this->view->firstname = $request->getRequest('firstname');
        } else
        {
            $this->view->firstname = '';
        }

        if ($request->issetPost('lastname'))
        {
            $this->view->lastname = $request->getRequest('lastname');
        } else
        {
            $this->view->lastname = '';
        }

        if ($request->issetPost('username'))
        {
            $this->view->username = $request->getRequest('username');
        } else
        {
            $this->view->username = '';
        }

        $this->view->password = $request->getRequest('password');
        $this->view->password2 = $request->getRequest('password2');
        /*
          } else
          {
          //no existe la sesión / no existe el dato recibido por post / el token no es igual.
          $return = array('error' => 401, 'description' => $this->translate('wrong token'));
          }


          error_log("callback " . $callback . " response " . $response);
         */
        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json; charset=utf-8');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    private function sendMail($userId)
    {
        $this->noViewRenderer(true);
        $this->noLayout(true);
        //$userId = '462';
        $user = Model_Users::getUser($userId);
        $url = WM_Router::create(JO_Request::getInstance()->getBaseUrl() . "?controller=welcome&action=finishRegistration&key=" . sha1($user['email'] . $user['username']));

        $body = "Hola y bienvenid@ a " . JO_Registry::get('site_name') . "! <br /> Para verificar tu email y finalizar el registro, por favor haz clic en el vinculo a continuación.<br/>  <br />Nota: Estamos solucionando un problema de compatibilidad con Hotmail y Outlook, en caso no puedas hacer clic en el siguiente vinculo, por favor cópialo, pégalo en la barra de direcciones y dale al ‘intro’. Disculpa las molestias.<br/> <br /><br/><a href=\"{$url}\">{$url}</a>";
        //var_dump($user);
        $to = $user['email'];
        $from = JO_Registry::forceGet('noreply_mail');
        $title = "amatteur - por favor verifica tu email";


        if (Model_Email::send($to, $from, $title, $body))
        {
            //$this->redirect(WM_Router::create(JO_Request::getInstance()->getBaseUrl()."?controller=users&action=verificationRequired"));
            return true;
        };
    }

    //====== user ====//
    public function usernoseusaAction()
    {
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();

        if (isset($_SESSION['token']) && isset($_POST['token']) && $_POST['token'] == $_SESSION['token'])
        {

//guardar o manipular datos.
            $result = Model_Users::checkLogin($request->getRequest('email'), $request->getRequest('password'));
            if ($result)
            {
                if ($result['status'])
                {
                    @setcookie('csrftoken_', md5($result['user_id'] . $request->getDomain() . $result['date_added']), (time() + ((86400 * 366) * 5)), '/', '.' . $request->getDomain());
                    JO_Session::set(array('user' => $result));
                    $return = array('id' => $result['user_id']); // $user_data;  
                } else
                {
                    $return = array('error' => 5, 'description' => $this->translate('This profile is not active.'));
                }
            } else
            {
                $return = array('error' => 6, 'description' => $this->translate('E-mail address and password do not match'));
            }
        } else
        {
//no existe la sesión / no existe el dato recibido por post / el token no es igual.
            $return = array('error' => 401, 'description' => $this->translate('wrong token'));
        }


        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json; charset=utf-8');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== pin detail ====//
    public function pindetailAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $limit = (int) $request->getRequest('limit');
        if ($limit < 1 || $limit > 100)
        {
            $limit = 50;
        } //$limit = JO_Registry::get('config_front_limit'); }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $data = array(
            'start' => ( $limit * $page ) - $limit,
            'limit' => $limit
        );

        $url = '&limit=' . $limit . '&page=' . ($page + 1);

        /*
          if($request->getRequest('popular') == 'true') {
          $data['filter_like_repin_comment'] = true;
          $url .= '&popular=true';
          }
          if( (int)$request->getRequest('fromPrice') ) {
          $data['filter_price_from'] = (int)$request->getRequest('fromPrice');
          $url .= '&fromPrice=' . (int)$request->getRequest('fromPrice');
          }
          if( (int)$request->getRequest('toPrice') ) {
          $data['filter_price_to'] = (int)$request->getRequest('toPrice');
          $url .= '&toPrice=' . (int)$request->getRequest('toPrice');
          }
          if( $request->getRequest('source') ) {
          $data['filter_source_id'] = $request->getRequest('source');
          $url .= '&source=' . $request->getRequest('source');
          }
         * 
         */
        if ($request->getRequest('pinId'))
        {
            $data['filter_pin_id'] = $request->getRequest('pinId');
            $url .= '&pinId=' . $request->getRequest('pinId');
        }
        /*
          if($request->issetRequest('video')) {
          $data['filter_is_video'] = (int)$request->getRequest('video');
          $url .= '&video=' . (int)$request->getRequest('video');
          }
          if( $request->getRequest('board') ) {
          $data['filter_board_id'] = $request->getRequest('board');
          $url .= '&board=' . $request->getRequest('board');
          }
         * 
         */
        if ($request->getRequest('userId'))
        {
            $data['filter_user_id'] = $request->getRequest('userId');
            $url .= '&user=' . $request->getRequest('userId');
        }
        /*
          if( $request->getRequest('search') ) {
          $data['filter_description'] = $request->getRequest('search');
          $url .= '&search=' . urlencode($request->getRequest('search'));
          }
          if( $request->getRequest('following') == 'true' ) {
          if(( $user_data = $this->isLoged() ) !== false) {
          $data['following_users_from_user_id'] = $user_data['user_id'];
          $url .= '&following=true';
          }
          }
         */
        $return = array();

        if (!$this->error)
        {
            $pins = Model_Pins::getPinsAPP($data);
            if ($pins)
            {
                $model_images = new Helper_Images();
                foreach ($pins AS $pin)
                {
                    $images = array();

                    $image = call_user_func(array(Helper_Pin::formatUploadModule($pin['store']), 'getPinImage'), $pin, '_B');
                    ;
                    if ($image)
                    {
                        $images['thumb']['src'] = $image['image'];
                        $images['thumb']['width'] = $image['width'];
                        $images['thumb']['height'] = $image['height'];
                        $images['original'] = $image['original'];
                    }

                    $categorias = array();

                    $categories = Model_Categories::getCategory($pin['category_id']);
                    if ($categories['parent_id'] != 0)
                    {
                        $categorias['category_id'] = $categories['parent_id'];
                        $categorias['subcategory_id'] = $categories['category_id'];
                    } else
                    {
                        $categorias['category_id'] = $categories['category_id'];
                        $categorias['subcategory_id'] = $categories['parent_id'];
                    }
                    //error_log("datos entrada ". $pin['pin_id'] ."-". $pin['user_id']);

                    $boards = Model_Boards::getBoardAPP($pin['board_id'], $pin['user_id'], "", WM_Router::create($request->getBaseUrl()), "pindetail");

                    $comments = Model_Pins::commentIsReportedAPP($pin['pin_id'], $pin['user_id']);

                    $likes = Model_Pins::pinIsLikedAPP($pin['pin_id']);

                    $shared = Model_Pins::repinAPP($pin['pin_id']);

                    $next_pin = Model_Pins::getNextPin($pin['pin_id']);
                    $next = "";
                    if ($next_pin)
                    {
                        $next = WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $next_pin['pin_id']);
                    }
                    $prev_pin = Model_Pins::getPrevPin($pin['pin_id']);
                    $prev = "";
                    if ($prev_pin)
                    {
                        $prev = WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $prev_pin['pin_id']);
                    }

//                                            error_log("valor boarder ". $comments,0)  ;                                        
//                                        if($comments)
//                                        {
//                                            error_log("valor board ". $comments,0)  ;
//                                        foreach ($comments as &$value)
//                                        {
//                                            error_log("valor board ". $value["title"],0)  ;
//                                        }
//                                        }
                    $pos = strripos($pin['image'], '.');

                    $return['data'][] = array(
                        'pin_id' => $pin['pin_id'],
                        'pinFooter' => $pin['description'],
                        'category_id' => $categorias['category_id'],
                        'subcategory_id' => $categorias['subcategory_id'],
                        //'source_id' => $pin['source_id'],
                        //'images' => $images,
                        /*
                          'pinImage' => $image['image'],
                          'pinWidth' => $image['width'],
                          'pinHeight' => $image['height'],
                         */
                        'pinImage' => substr_replace($pin['image'], '_B.', $pos, 1),
                        'pinWidth' => $pin['width'],
                        'pinHeight' => $pin['height'],
                        'pinUrl' => WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id']),
                        'comments' => $comments ? $comments : "",
                        'likes' => $likes ? $likes : "",
                        'folder' => $boards ? array($boards['title'], WM_Router::create($request->getBaseUrl() . $pin['username'] . "/" . $boards['title']), $boards['image']) : "",
                        'shared' => $shared ? $shared : "",
                        'next' => $next ? $next : "",
                        'previous' => $prev ? $prev : "",
                        //'repins' => $pin['repins'],
                        'userId' => $pin['user_id'],
                        'username' => $pin['username'],
                        'avatar' => $pin['avatar'],
                        'userLike' => $pin['liked'],
                        'pinVideo' => $pin['from'],
                            //etc		
                    );
                }
                $return['next_page'] = WM_Router::create($request->getBaseUrl() . '?controller=apiv1&action=pindetail' . $url);
            }
        } else
        {
            $return = array('error' => $this->error);
        }

        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== category ====//
    public function categoryAction()
    {
        //echo"entrando";
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();

        //////////// Categories ////////////
        //$this->view->categories = array();
        //$this->view->category_active = false;
        //array('id' => $result['user_id']); // $user_data;
        $categories = Model_Categories::getCategories(array(
                    'filter_status' => 1
                ));

        /*
          foreach($categories AS $category) {
          $category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
          $category['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=category&category_id=' . $category['category_id'] );
          $category['active'] = $category['category_id'] == $request->getRequest('category_id');
          if($category['active']) {
          //$this->view->category_active = $category['title'];
          } else {

          }

          //$this->view->categories[] = $category;
          echo "sub ". $category['subcategories'];
          }
         */
        $return = array($categories);

        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json; charset=utf-8');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== subcategory ====//
    public function subcategoryAction()
    {
        //echo"entrando";
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();

        $subCategories = "";
        if ($request->getRequest('category_id') != "")
        {
            $subCategories = Model_Categories::getSubCategoriesAPP($request->getRequest('category_id'));

            error_log("subcats ");

            if ($subCategories)
            {
                foreach ($subCategories AS $subCategorie)
                {
                    $return['data'][] = array(
                        'category_id' => $subCategorie['category_id'],
                        'title' => $subCategorie['title'],
                        'sort_order' => $subCategorie['sort_order'],
                        'link' => WM_Router::create($request->getBaseUrl() . '?controller=category&category_id=' . $subCategorie['category_id'])
                    );
                }
            }
        } else
        {
            $return = array('error' => 7, 'description' => 'Subcategoría vacía');
        }

        /*
          foreach($categories AS $category) {
          $category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
          $category['href'] = WM_Router::create( $request->getBaseUrl() . '?controller=category&category_id=' . $category['category_id'] );
          $category['active'] = $category['category_id'] == $request->getRequest('category_id');
          if($category['active']) {
          //$this->view->category_active = $category['title'];
          } else {

          }

          //$this->view->categories[] = $category;
          echo "sub ". $category['subcategories'];
          }
         */



        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json; charset=utf-8');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== User Info ====//
    public function userinfoAction()
    {


        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }


        $data = array();
        $url = "";

        $data['filter_user_id'] = $request->getRequest('userId');
        $url .= '&user=' . $request->getRequest('userId');

        $return = array();

        $userinfo = Model_Users::getUser($request->getRequest('userId'), false, Model_Users::$allowed_fields);
        if ($userinfo)
        {
            $boards = Model_Boards::getBoardAPP("", $userinfo['user_id'], $userinfo['username'], WM_Router::create($request->getBaseUrl()), "userinfo");

            $result['userId'] = $userinfo["user_id"];
            $result['userName'] = $userinfo["username"];
            $result['userIcon'] = $userinfo["avatar"];
            $result['userDescription'] = $userinfo["username"];
            $result['folderQty'] = Model_Boards::getTotalBoards($data);
            ;
            $result['imageQty'] = Model_Pins::getTotalPins($data);
            $result['likeQty'] = $userinfo["likes"];
            $result['followingQty'] = $userinfo["following"];
            $result['followersQty'] = $userinfo["followers"];
            $result['folders'] = $boards ? $boards : "";
            $return[] = $result;
        } else
        {
            $return = array('error' => $this->error);
        }

        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== timeline ====//
    public function timelineAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $limit = (int) $request->getRequest('limit');
        if ($limit < 1 || $limit > 100)
        {
            $limit = 50;
        } //$limit = JO_Registry::get('config_front_limit'); }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $data = array(
            'start' => ( $limit * $page ) - $limit,
            'limit' => $limit
        );

        $url = '&limit=' . $limit . '&page=' . ($page + 1);

        /*
          if($request->getRequest('popular') == 'true') {
          $data['filter_like_repin_comment'] = true;
          $url .= '&popular=true';
          }
          if( (int)$request->getRequest('fromPrice') ) {
          $data['filter_price_from'] = (int)$request->getRequest('fromPrice');
          $url .= '&fromPrice=' . (int)$request->getRequest('fromPrice');
          }
          if( (int)$request->getRequest('toPrice') ) {
          $data['filter_price_to'] = (int)$request->getRequest('toPrice');
          $url .= '&toPrice=' . (int)$request->getRequest('toPrice');
          }
          if( $request->getRequest('source') ) {
          $data['filter_source_id'] = $request->getRequest('source');
          $url .= '&source=' . $request->getRequest('source');
          }
         * 
         */
        if ($request->getRequest('categoryId'))
        {
            $data['filter_category_id'] = $request->getRequest('categoryId');
            $url .= '&categoryId=' . $request->getRequest('categoryId');
        }
        /*
          if($request->issetRequest('video')) {
          $data['filter_is_video'] = (int)$request->getRequest('video');
          $url .= '&video=' . (int)$request->getRequest('video');
          }
         */
        if ($request->getRequest('board'))
        {
            $data['filter_board_id'] = $request->getRequest('folderId');
            $url .= '&board=' . $request->getRequest('folderId');
        }
        if ($request->getRequest('userId'))
        {
            $data['filter_user_id'] = $request->getRequest('userId');
            $url .= '&user=' . $request->getRequest('userId');
        }
        /*
          if( $request->getRequest('search') ) {
          $data['filter_description'] = $request->getRequest('search');
          $url .= '&search=' . urlencode($request->getRequest('search'));
          }
          if( $request->getRequest('following') == 'true' ) {
          if(( $user_data = $this->isLoged() ) !== false) {
          $data['following_users_from_user_id'] = $user_data['user_id'];
          $url .= '&following=true';
          }
          }
         */
        $return = array();

        //if (isset($_POST['token']) && $_POST['token'] == md5($_POST['user_id']))
        //{
        //$_SESSION['token'] = $_POST['token'];

        if (!$this->error)
        {
            $pins = Model_Pins::getPinsAPP($data);
            if ($pins)
            {
                $model_images = new Helper_Images();
                foreach ($pins AS $pin)
                {
                    $images = array();

                    $image = call_user_func(array(Helper_Pin::formatUploadModule($pin['store']), 'getPinImage'), $pin, '_B');
                    if ($image)
                    {
                        $images['thumb']['src'] = $image['image'];
                        $images['thumb']['width'] = $image['width'];
                        $images['thumb']['height'] = $image['height'];
                        $images['original'] = $image['original'];
                    }


                    $categorias = array();
                    $categories = Model_Categories::getCategory($pin['category_id']);
                    if ($categories['parent_id'] != 0)
                    {
                        $categorias['category_id'] = $categories['parent_id'];
                        $categorias['subcategory_id'] = $categories['category_id'];
                    } else
                    {
                        $categorias['category_id'] = $categories['category_id'];
                        $categorias['subcategory_id'] = $categories['parent_id'];
                    }

                    $pos = strripos($pin['image'], '.');

                    $return['data'][] = array(
                        'pin_id' => $pin['pin_id'],
                        'pinFooter' => $pin['description'],
                        'category_id' => $categorias['category_id'],
                        'subcategory_id' => $categorias['subcategory_id'],
                        //'source_id' => $pin['source_id'],
                        //'images' => $images,
                        /*
                          'pinImage' => $image['image'],
                          'pinWidth' => $image['width'],
                          'pinHeight' => $image['height'],
                         */
                        'pinImage' => substr_replace($pin['image'], '_B.', $pos, 1),
                        'pinWidth' => $pin['width'],
                        'pinHeight' => $pin['height'],
                        'pinUrl' => WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id']),
                        //'comments' => $pin['comments'],
                        //'likes' => $pin['likes'],
                        //'repins' => $pin['repins'],
                        'userId' => $pin['user_id'],
                        'username' => $pin['username'],
                        'avatar' => $pin['avatar'],
                        'userLike' => $pin['liked'],
                        'pinVideo' => $pin['from'],
                            //'width' => $img_size[0],
                            //'height' => $img_size[1]
                            //etc		
                    );
                }
                $return['next_page'] = WM_Router::create($request->getBaseUrl() . '?controller=apiv1&action=timeline' . $url);
            }
        } else
        {
            $return = array('error' => $this->error);
        }
        //} else
        //{
//no existe la sesión / no existe el dato recibido por post / el token no es igual.
        //    $return = array('error' => 401, 'description' => $this->translate('wrong token'));
        //}


        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== Carpeta ====//
    public function carpetaAction()
    {
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }


        $data = array();
        $url = "";

        $data['filter_folder_id'] = $request->getRequest('folderId');
        $url .= '&board_id=' . $request->getRequest('folderId');

        $return = array();

        $boards = Model_Boards::getBoardAPP($request->getRequest('folderId'), "", "", WM_Router::create($request->getBaseUrl()), "carpeta");
        $return[] = $boards;

        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //=========== Search ==============//
    public function searchAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }


        $data = array();
        $url = "";

        $return = array();

        $kind = $request->getRequest('kind');
        $query = $request->getRequest('query');
        $userId = $request->getRequest('userId');
        if ($kind == 0)
        {
            // pins
            $data = array(
                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                'limit' => JO_Registry::get('config_front_limit'),
                'filter_description' => $query,
                'filter_marker' => $request->getRequest('marker')
            );

            $this->view->pins = '';

            $pins = Model_Pins::getPinsAPP($data);
            if ($pins)
            {
                $model_images = new Helper_Images();
                foreach ($pins AS $pin)
                {
                    $images = array();

                    $image = call_user_func(array(Helper_Pin::formatUploadModule($pin['store']), 'getPinImage'), $pin, '_B');
                    if ($image)
                    {
                        $images['thumb']['src'] = $image['image'];
                        $images['thumb']['width'] = $image['width'];
                        $images['thumb']['height'] = $image['height'];
                        $images['original'] = $image['original'];
                    }


                    $categorias = array();
                    $categories = Model_Categories::getCategory($pin['category_id']);
                    if ($categories['parent_id'] != 0)
                    {
                        $categorias['category_id'] = $categories['parent_id'];
                        $categorias['subcategory_id'] = $categories['category_id'];
                    } else
                    {
                        $categorias['category_id'] = $categories['category_id'];
                        $categorias['subcategory_id'] = $categories['parent_id'];
                    }

                    $pos = strripos($pin['image'], '.');

                    $return['data'][] = array(
                        'pin_id' => $pin['pin_id'],
                        'pinFooter' => $pin['description'],
                        'category_id' => $categorias['category_id'],
                        'subcategory_id' => $categorias['subcategory_id'],
                        //'source_id' => $pin['source_id'],
                        //'images' => $images,
                        /*
                          'pinImage' => $image['image'],
                          'pinWidth' => $image['width'],
                          'pinHeight' => $image['height'],
                         */
                        'pinImage' => substr_replace($pin['image'], '_B.', $pos, 1),
                        'pinWidth' => $pin['width'],
                        'pinHeight' => $pin['height'],
                        'pinUrl' => WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $pin['pin_id']),
                        //'comments' => $pin['comments'],
                        //'likes' => $pin['likes'],
                        //'repins' => $pin['repins'],
                        'userId' => $pin['user_id'],
                        'username' => $pin['username'],
                        'avatar' => $pin['avatar'],
                        'userLike' => $pin['liked'],
                        'pinVideo' => $pin['from'],
                            //'width' => $img_size[0],
                            //'height' => $img_size[1]
                            //etc		
                    );
                }
                $return['next_page'] = WM_Router::create($request->getBaseUrl() . '?controller=apiv1&action=timeline' . $url);
            }
        } else if ($kind == 1)
        {

            //boards
            $data = array(
                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                'limit' => JO_Registry::get('config_front_limit'),
                'filter_title' => $query
            );

            $this->view->pins = '';

            $boards = Model_Boards::getBoards($data);
            if ($boards)
            {
                $view = JO_View::getInstance();
                $view->loged = JO_Session::get('user[user_id]');
                $view->enable_sort = false;
                $model_images = new Helper_Images();
                foreach ($boards AS $board)
                {

                    $board['href'] = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board['user_id'] . '&board_id=' . $board['board_id']);
                    $board['thumbs'] = array();
                    $get_big = false;
                    for ($i = 0; $i < 5; $i++)
                    {
                        $image = isset($board['pins_array'][$i]) ? $board['pins_array'][$i]['image'] : false;
                        if ($image)
                        {
                            if ($get_big)
                            {
                                $size = '_A';
                            } else
                            {
                                $size = '_C';
                                $get_big = true;
                            }
                            $data_img = Helper_Uploadimages::pin($board['pins_array'][$i], $size);
                            if ($data_img)
                            {
                                $board['thumbs'][] = $data_img['image'];
                            } else
                            {
                                $board['thumbs'][] = false;
                            }
                        } else
                        {
                            $board['thumbs'][] = false;
                        }
                    }

                    $board['boardIsFollow'] = Model_Users::isFollow(array(
                                'board_id' => $board['board_id']
                            ));

                    $board['userFollowIgnore'] = $board['user_id'] != JO_Session::get('user[user_id]');

                    $board['follow'] = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=follow&user_id=' . $board['user_id'] . '&board_id=' . $board['board_id']);

                    $board['edit'] = false;
                    if ($board['user_id'] == JO_Session::get('user[user_id]') || Model_Boards::allowEdit($board['board_id']))
                    {
                        $board['userFollowIgnore'] = false;
                        $board['edit'] = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=edit&user_id=' . $board['user_id'] . '&board_id=' . $board['board_id']);
                    }

                    $username = Model_Users::getUsername($board["user_id"]);

                    $url_base = WM_Router::create($request->getBaseUrl());

                    $return['data'][] = array(
                        'folderId' => $board['board_id'],
                        'folderName' => $board['title'],
                        'folderUrl' => $url_base . $username . "/" . $board['title'],
                        'folderImage' => $board['image'],
                        'folderQty' => $board['pins']
                    );


                    //$return[] = $board;
                }
            }
        } else if ($kind == 2)
        {

            //people
            $data = array(
                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                'limit' => JO_Registry::get('config_front_limit'),
                'filter_username' => $query
            );


            $users = Model_Users::getUsers($data);
            if ($users)
            {
                $this->view->follow_user = true;
                $view = JO_View::getInstance();
                $view->loged = JO_Session::get('user[user_id]');
                $model_images = new Helper_Images();
                foreach ($users AS $key => $user)
                {
                    $avatar = Helper_Uploadimages::avatar($user, '_B');
                    $user['avatar'] = $avatar['image'];

                    if ($view->loged)
                    {
                        $user['userIsFollow'] = Model_Users::isFollowUser($user['user_id']);
                        $user['userFollowIgnore'] = $user['user_id'] == JO_Session::get('user[user_id]');
                    } else
                    {
                        $user['userFollowIgnore'] = true;
                    }

                    $user['href'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
                    $user['follow'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id']);

                    $return['data'][] = array(
                        'userId' => $user['user_id'],
                        'userName' => $user['username'],
                        'userDesc' => $user['description'],
                        'userLocation' => $user['location'],
                        'avatar' => $user['avatar'],
                        'follower' => $user['followers'],
                        'following' => $user['following']
                    );
                }
            }
        }





        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //===== COMMENTS =====//
    public function commentsAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();

        $pin_id = (int) $request->getRequest('pinId');
        $pin_info = Model_Pins::getPin($pin_id);
        if (!$pin_info)
        {
            $return = array('error' => $this->translate('There was a problem with the record. Please try again!'));
        } else
        {

            if ($request->getParam('comments') == 'post')
            {

                if ($this->isLoged())
                {

                    ///add comment
                } else
                {
                    $return = array('error' => $this->error);
                }
            } else
            {

                $data = array(
                    'filter_pin_id' => $pin_id
                );

                $return = array('data' => array());

                $comments = Model_Comments::getComments($data);

                if ($comments)
                {
                    foreach ($comments AS $comment)
                    {
                        $return['data'][] = array(
                            'userId' => $comment['user']['user_id'],
                            'userName' => $comment['user']['username'],
                            'avatar' => $comment['user']['avatar'],
                            'comment' => $comment['comment']
                                //etc
                        );
                    }
                }
            }
        }


        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== añadir comment ====//
    public function commentAction()
    {
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $pin_id = $request->getRequest('pinId');
        $user_id = $request->getRequest('userId');

        $pin_info = Model_Pins::getPin($pin_id);

        $return = array();

        if (!$pin_info)
        {
            $return['data'][] = array(
                'error' => 8, 'description' => "no existe pin con ese id"
            );
        }

        //if($request->isPost()) {

        $data = $request->getParams();
        //$write_comment = $request->getPost('comment');

        $commentId = Model_Pins::addCommentAPP($data, $pin_info['latest_comments'], Model_Users::$allowed_fields);

        $return['data'][] = array(
            'commentId' => $commentId
        );


        //}

        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //=========== seguidores y seguidos ==============//
    public function followersAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }


        $data = array();
        $url = "";

        $return = array();

        $followers = $request->getRequest('followers');
        $userId = $request->getRequest('userId');

        if ($followers == 1)
        {
            $data = Model_Users::followingUsers($userId);
        } else if ($followers == 0)
        {
            $data = Model_Users::followersUsers($userId);
        }



        if ($data)
        {

            foreach ($data AS $key => $user)
            {
                $dota['filter_user_id'] = $user["user_id"];
                $url .= '&user=' . $user["user_id"];

                $user = Model_Users::getUser($user["user_id"], false, Model_Users::$allowed_fields);

                if ($user)
                {
                    $avatar = Helper_Uploadimages::avatar($user, '_B');
                    $user['avatar'] = $avatar['image'];

                    $return['data'][] = array(
                        'userId' => $user['user_id'],
                        'userName' => $user['username'],
                        'userDesc' => $user['description'],
                        'userLocation' => $user['location'],
                        'avatar' => $user['avatar'],
                        'follower' => $user['followers'],
                        'following' => $user['following']
                    );
                }
            }
        }


        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //=========== seguir y dejar de seguir ==============//
    public function followAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();

        $action = $request->getRequest('accion');
        $userId = $request->getRequest('userId');
        $followerId = $request->getRequest('followerId');

        if ($action == 1)
        {
            $data = Model_Users::FollowUserAPP($userId, $followerId);
        } else if ($action == 0)
        {
            $data = Model_Users::UnFollowUserAPP($userId, $followerId);
        }

        if ($data)
        {
            $return = $data;
        } else
        {
            $return = "error";
        }

        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== recuperar folder ====//
    public function foldersAction()
    {
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }



        if (isset($_POST['token']) && $_POST['token'] == md5($_POST['userId']))
        {
            $_SESSION['token'] = $_POST['token'];
            JO_Session::set('token', $_POST['token']);


//        $token = $request->getRequest('token');
            $user_id = $request->getRequest('userId');

//        $token = $request->getRequest('token');
//        $user_id = $request->getRequest('userId');        
//        
//        
//        error_log("token " .$token);
//        error_log("user " . md5($user_id));
//        error_log("session " . $_SESSION['token']) ;
//        
//        if (isset($token) && $token == md5($user_id))
//        {
//            $_SESSION['token'] = $token;




            $return = array();

            //if($request->isPost()) {
            //$data = $request->getParams();
            //$write_comment = $request->getPost('comment');

            $boards = Model_Boards::getBoardAPP("", $user_id, "", WM_Router::create($request->getBaseUrl()), "folders");

            $return = $boards;


            //}
        } else
        {
//no existe la sesión / no existe el dato recibido por post / el token no es igual.
            $return = array('error' => 401, 'description' => $this->translate('wrong token'));
        }


        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== añadir folder ====//
    public function newfolderAction()
    {
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }



        if (isset($_POST['token']) && $_POST['token'] == md5($_POST['userId']))
        {
            $_SESSION['token'] = $_POST['token'];
            JO_Session::set('token', $_POST['token']);


            //        $token = $request->getRequest('token');
//            $user_id = $request->getRequest('userId');    
//            $folderName = $request->getRequest('folderName');            
//            $categoryId = $request->getRequest('categoryId');                        
//
//            $token = $request->getRequest('token');
            //$user_id = $request->getRequest('userId');        
//            error_log("token " .$token);
//            error_log("user " . md5($user_id));
//            error_log("session " . $_SESSION['token']) ;
//            if (isset($token) && $token == md5($user_id))
//            {
//                $_SESSION['token'] = $token;




            $return = array();

            //if($request->isPost()) {
            //$data = $request->getParams();
            //$write_comment = $request->getPost('comment');
            $board_id = Model_Boards::getBoardIdAPP(array(
                        'title' => trim($request->getPost('folderName')),
                        'category_id' => $request->getPost('categoryId'),
                        'user_id' => $request->getPost('userId')
                    ));

            if ($board_id == 0)
            {
                $board_id = array('error' => 9, 'description' => $this->translate('folderName exists with the same name'));
            }

            $return = $return = array('folderId' => $board_id);


            //}
        } else
        {
//no existe la sesión / no existe el dato recibido por post / el token no es igual.
            $return = array('error' => 401, 'description' => $this->translate('wrong token'));
        }


        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //====== subir fichero ====//
    public function uploadAction()
    {
        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }



        if (isset($_POST['token']) && $_POST['token'] == md5($_POST['userId']))
        {
            $_SESSION['token'] = $_POST['token'];
            JO_Session::set('token', $_POST['token']);


            //        $token = $request->getRequest('token');
//            $user_id = $request->getRequest('userId');    
//            $folderName = $request->getRequest('folderName');            
//            $categoryId = $request->getRequest('categoryId');                        
//
//            $token = $request->getRequest('token');
            //$user_id = $request->getRequest('userId');        
//            error_log("token " .$token);
//            error_log("user " . md5($user_id));
//            error_log("session " . $_SESSION['token']) ;
//            if (isset($token) && $token == md5($user_id))
//            {
//                $_SESSION['token'] = $token;




            $return = array();



            //print_r("files " . var_dump($_FILES))   ;
            //print_r("request " .var_dump($_REQUEST));
            //error_log("1file name " . $_FILES["file"]["tmp_name"] . " uploads " . $_REQUEST["image"]);
            //error_log("2file name " . $_FILES["uploadedfile"]["name"] . " uploads " . $_REQUEST["image"]); 
            //$this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=upload_images' );
            //$this->view->upload_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=upload_imagesView' );
            //$this->view->popup_main_box = $this->view->render('fromfile','addpin');

            if (JO_Session::get('upload_from_file'))
            {
                @unlink(BASE_PATH . JO_Session::get('upload_from_file'));
                JO_Session::clear('upload_from_file');
                JO_Session::clear('upload_from_file_name');
            }

            $image = $request->getFile('file');
            if (!$image)
            {
                $return = array('error' => 10, 'description' => $this->translate('There is no file selected'));
            } else
            {

                $temporary = '/cache/review/';
                $upload_folder = BASE_PATH . $temporary;
                $upload = new Helper_Upload;

                $upload->setFile($image)
                        ->setExtension(array('.jpg', '.jpeg', '.png', '.gif'))
                        ->setUploadDir($upload_folder);
                $new_name = md5(time() . serialize($image));
                if ($upload->upload($new_name))
                {
                    $info = $upload->getFileInfo();
                    if ($info)
                    {

                        $this->view->from_url = WM_Router::create($request->getBaseUrl() . '?controller=addpin&action=fromfile');

//						$this->view->file = $image['name'];
//						$this->view->full_path = $temporary . $info['name'];
                        $this->view->success = 1; //$this->view->render('upload_images', 'addpin');
                        JO_Session::set('upload_from_file', $temporary . $info['name']);
                        JO_Session::set('upload_from_file_name', $image['name']);
                    } else
                    {
                        $return = array('error' => 11, 'description' => $this->translate('An unknown error'));
                    }
                } else
                {
                    $return = array('error' => 12, 'description' => $upload->getError());
                }
            }

            if ($request->isPost())
            {

                $result = Model_Pins::create(array(
                            'title' => $request->getPost('title'),
                            'from' => '',
                            'image' => BASE_PATH . JO_Session::get('upload_from_file'),
                            'is_video' => $request->getPost('is_video'),
                            'is_article' => $request->getPost('is_article'),
                            'description' => $request->getPost('message'),
                            'price' => $request->getPost('price'),
                            'board_id' => $request->getPost('board_id')
                        ));
                if ($result)
                {

                    Model_History::addHistory(0, Model_History::ADDPIN, $result);

                    if (JO_Registry::get('isMobile'))
                    {
                        //$this->redirect('/');
                    }

                    $session_user = JO_Session::get('user[user_id]');

                    $group = Model_Boards::isGroupBoard($request->getPost('board_id'));
                    if ($group)
                    {
                        $users = explode(',', $group);
                        foreach ($users AS $user_id)
                        {
                            if ($user_id != $session_user)
                            {
                                $user_data = Model_Users::getUser($user_id);

                                if ($user_data && $user_data['email_interval'] == 1 && $user_data['groups_pin_email'])
                                {
                                    $this->view->user_info = $user_data;
                                    $this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
                                    $this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
                                    $this->view->pin_href = WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $result);
                                    $board_info = Model_Boards::getBoard($request->getPost('board_id'));
                                    if ($board_info)
                                    {
                                        $this->view->board_title = $board_info['title'];
                                        $this->view->board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id']);
                                    }
                                    Model_Email::send(
                                            $user_data['email'], JO_Registry::get('noreply_mail'), JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('added new pin to a group board'), $this->view->render('group_board', 'mail')
                                    );
                                }
                            }
                        }
                    }

                    $this->view->pin_url = WM_Router::create($request->getBaseUrl() . '?controller=pin&pin_id=' . $result);
                    $this->view->popup_main_box = $this->view->render('success', 'addpin');
                    if (JO_Session::get('upload_from_file'))
                    {
                        @unlink(BASE_PATH . JO_Session::get('upload_from_file'));
                        JO_Session::clear('upload_from_file');
                        JO_Session::clear('upload_from_file_name');
                    }
                }
            }





            /*
              //$_FILES-> name type tmp_name error size
              //'image' => BASE_PATH . JO_Session::get('upload_from_file'),
              if( $request->isPost() ) {
              $this->view->form_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=upload_images' );

              $this->view->upload_action = WM_Router::create( $request->getBaseUrl() . '?controller=addpin&action=upload_imagesView' );



              $this->view->popup_main_box = $this->view->render('fromfile','addpin');


              $url_m = $request->getPost('image');
              if(strpos($url_m, '.jpg?')) {
              $url_m = explode('?', $url_m);
              $url_m = $url_m[0];
              }
              error_log("3file name " . $_FILES["file"]["tmp_name"] . " url_m " . $url_m);
              $url_m = $_FILES;

              $result = Model_Pins::create(array(
              'title' => $request->getPost('title'),
              'from' => $request->getPost('from'),
              'image' => $url_m,
              'is_video' => 0, //$request->getPost('is_video'),
              'is_article' => 0, //$request->getPost('is_article'),
              'description' => $request->getPost('message'),
              'price' => $request->getPost('price'),
              'board_id' => $request->getPost('board_id')
              ));
              if($result) {
              Model_History::addHistory(JO_Session::get('user[user_id]'), Model_History::ADDPIN, $result);


              $session_user = JO_Session::get('user[user_id]');

              $group = Model_Boards::isGroupBoard($request->getPost('board_id'));
              if($group) {
              $users = explode(',',$group);
              foreach($users AS $user_id) {
              if($user_id != $session_user) {
              $user_data = Model_Users::getUser($user_id);

              if($user_data && $user_data['email_interval'] == 1 && $user_data['groups_pin_email']) {
              $this->view->user_info = $user_data;
              $this->view->profile_href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]'));
              $this->view->full_name = JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]');
              $this->view->pin_href = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $result );
              $board_info = Model_Boards::getBoard($request->getPost('board_id'));
              if($board_info) {
              $this->view->board_title = $board_info['title'];
              $this->view->board_href = WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board_info['user_id'] . '&board_id=' . $board_info['board_id']);
              }
              Model_Email::send(
              $user_data['email'],
              JO_Registry::get('noreply_mail'),
              JO_Session::get('user[firstname]') . ' ' . JO_Session::get('user[lastname]') . ' ' . $this->translate('added new pin to a group board'),
              $this->view->render('group_board', 'mail')
              );
              }

              }
              }
              }

              $this->view->pin_url = WM_Router::create( $request->getBaseUrl() . '?controller=pin&pin_id=' . $result );
              $this->view->popup_main_box = $this->view->render('success','addpin');
              }

              }
             */
        } else
        {
//no existe la sesión / no existe el dato recibido por post / el token no es igual.
            $return = array('error' => 401, 'description' => $this->translate('wrong token'));
        }


        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    //===== logout ======//
    public function logoutAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        if (isset($_POST['token']) && $_POST['token'] == md5($_POST['userId']))
        {
            $_SESSION['token'] = NULL;
            JO_Session::set('token', NULL);

            if ($_POST['userId'] == JO_Session::get('user[user_id]'))
            {
                $this->setInvokeArg('noViewRenderer', true);
                @setcookie('csrftoken_', md5(JO_Session::get('user[user_id]') . $this->getRequest()->getDomain() . JO_Session::get('user[date_added]')), (time() - 100), '/', '.' . $this->getRequest()->getDomain());
                JO_Session::set(array('user' => false));

                $return = 1;
            } else
            {
                $return = 0;
            }
        } else
        {
//no existe la sesión / no existe el dato recibido por post / el token no es igual.
            $return = array('error' => 401, 'description' => $this->translate('wrong token'));
        }


        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    public function registerfbAction()
    {


        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();

        /*
        if (JO_Session::get('user[user_id]'))
        {
            $this->redirect(WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]')));
        }

        if (!$data)
        {
            $this->redirect(WM_Router::create($this->getRequest()->getBaseUrl() . '?controller=users&action=login'));
        }
         * 
         */

        //$fbData = $data['fbData'];
        //$session = $data['session'];
        //$shared_content = isset($data['shared_content']) ? $data['shared_content'] : '';
        $shared_content = Model_Users::checkSharedContent($request->getParam('key'), $request->getParam('user_id'));


        //self::loginInit($fbData['id'], $session);

        $ph = new WM_Facebook_Photo();
        $image = $ph->getRealUrl('http://graph.facebook.com/' . $request->getPost('facebook_id') . '/picture?type=large');
        if (!@getimagesize($image))
        {
            $image = '';
        }

        $this->view->error = false;
        $session = $request->getPost('facebook_id');
        
        if ($request->isPost())
        {

            $validate = new Helper_Validate();
            $validate->_set_rules($request->getPost('username'), $this->translate('Username'), 'not_empty;min_length[3];max_length[100];username');
//			$validate->_set_rules($request->getPost('firstname'), $this->translate('First name'), 'not_empty;min_length[3];max_length[100]');
//			$validate->_set_rules($request->getPost('lastname'), $this->translate('Last name'), 'not_empty;min_length[3];max_length[100]');
            $validate->_set_rules($request->getPost('email'), $this->translate('Email'), 'not_empty;min_length[5];max_length[100];email');
            $validate->_set_rules($request->getPost('password'), $this->translate('Password'), 'not_empty;min_length[4];max_length[30]');
//			$validate->_set_rules($request->getPost('password2'), $this->translate('Confirm password'), 'not_empty;min_length[4];max_length[30]');

            if ($validate->_valid_form())
            {
                if( md5($request->getPost('password')) != md5($request->getPost('password2')) ) {
                        $validate->_set_form_errors( $this->translate('Password and Confirm Password should be the same') );
                        $validate->_set_valid_form(false);
                }
                if (Model_Users::isExistEmail($request->getPost('email')))
                {
                    $validate->_set_form_errors($this->translate('This e-mail address is already used'));
                    $validate->_set_valid_form(false);
                }
                if (Model_Users::isExistUsername($request->getPost('username')))
                {
                    $validate->_set_form_errors($this->translate('This username is already used'));
                    $validate->_set_valid_form(false);
                }
            }

            if ($validate->_valid_form())
            {
                $reg_key = sha1($request->getPost('email') . $request->getPost('username'));

                $result = Model_Users::create(array(
                            'facebook_id' => $request->getPost('facebook_id'),
                            //'gender' => (isset($request->getPost('gender')) ? $request->getPost('gender') : ''),
                            'gender' => $request->getPost('gender'),
                            'avatar' => ($image ? $image : ''),
                            //'location' => (isset($request->getPost('location')) ? $request->getPost('location') : ''),
                            //'website' => (isset($request->getPost('website')) ? $request->getPost('website') : ''),
                            'location' => $request->getPost('location'),
                            'website' => $request->getPost('website'),
                            'username' => $request->getPost('username'),
                            //'firstname' => isset($request->getPost('first_name')) ? $request->getPost('first_name') : '',
                            //'lastname' => isset($request->getPost('last_name')) ? $request->getPost('last_name') : '',
                            'firstname' => $request->getPost('first_name'),
                            'lastname' => $request->getPost('last_name') ,
                            'email' => $request->getPost('email'),
                            'password' => $request->getPost('password'),
                            //'delete_email' => isset($request->getPost('email')) ? $request->getPost('email') : '',
                            'delete_email' => $request->getPost('email'),                    
                            'facebook_session' => $session,
                            'delete_code' => isset($shared_content['if_id']) ? $shared_content['if_id'] : '',
                            'following_user' => isset($shared_content['user_id']) ? $shared_content['user_id'] : '',
                            'facebook_connect' => 1,
                            'confirmed' => '0',
                            'regkey' => $reg_key
                        ));

                if ($result)
                {
                    if (self::sendMail($result))
                    {
                        //self::loginInit($result);
                    };
                    $return = array('id' => $result); //['user_id']); 
                } else
                {
                    $return = array('error' => 3, 'description' => $this->translate('There was a problem with the record. Please try again!'));
                }
            } else
            {
                $return = array('error' => 4, 'description' => $validate->_get_error_messages());
            }
        }

        $this->view->user_id_fb = $request->getPost('facebook_id');


        $this->view->baseUrl = $request->getBaseUrl();

        if ($request->issetPost('email'))
        {
            $this->view->email = $request->getPost('email');
        } 
        else
        {
                $this->view->email = '';
        }

        if ($request->issetPost('firstname'))
        {
            $this->view->firstname = $request->getPost('firstname');
        } 
        else
        {
            $this->view->firstname = '';
        }

//		
//		if($request->issetPost('lastname')) {
//			$this->view->lastname = $request->getPost('lastname');
//		} else {
//			if(isset($fbData['last_name'])) {
//				$this->view->lastname = $fbData['last_name'];
//			} else {
//				$this->view->lastname = '';
//			}
//		}

        if ($request->issetPost('username'))
        {
            $this->view->username = $request->getPost('username');
        } else
        {
                $this->view->username = '';
        }

        $this->view->password = $request->getPost('password');
//		$this->view->password2 = $request->getPost('password2');


        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json; charset=utf-8');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    public function loginfbAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();


//		if( JO_Session::get('user[user_id]') ) {
//			$this->redirect( WM_Router::create( $request->getBaseUrl() . '?controller=users&action=profile&user_id=' . JO_Session::get('user[user_id]') ) );
//		}

        /* if($request->getQuery('session')) {
          $session = JO_Json::decode( html_entity_decode($request->getQuery('session')), true );
          if($session) {
          $this->facebook->setSession($session);
          if($request->getQuery('next')) {
          JO_Session::set('next', $request->getQuery('next'));
          }
          }
          } */
        if (isset($_POST['facebook_id']))
        {
            $id = $_POST['facebook_id'];

            $user_data = WM_Users::checkLoginFacebookTwitter($id, 'facebook');
            if ($user_data)
            {
                JO_Session::set(array('user' => $user_data));
                JO_Session::clear('fb_login');

                $token = md5($user_data['user_id']);

                $_SESSION['token'] = $token;
                JO_Session::set('token', $token);


                $return = array('id' => $user_data['user_id'],
                    'username' => $user_data['username'],
                    'token' => $token,
                    'firstname' => $user_data['firstname'],
                    'lastname' => $user_data['lastname']);
            } 
            else
            {
                $return = array('error' => 13, 'description' => $this->translate("Error en el login de facebook"));
            }
        }

        /*
          $session = $this->facebook->getUser();

          $fbData = null;
          if($session) {
          $fbData = $this->facebook->api('/me');
          }


          if($fbData) {
          if(!isset($fbData['email'])) {
          $fbData['email'] = '';
          }

          if(!self::loginInit($fbData['id'], $session)) {

          //if(!self::loginInit($fbData['email'], $session, 'email')) {

          if(JO_Registry::get('enable_free_registration')) {
          $this->forward('facebook', 'register', array('fbData'=>$fbData, 'session' => $session, 'shared_content' => array()));
          }

          $shared_content = Model_Users::checkInvateFacebookID($fbData['id']);

          if( $shared_content ) {
          $token = md5($result['user_id']);

          $_SESSION['token'] = $token;
          JO_Session::set('token', $token);
          //$token = md5(uniqid(rand(), true));

          $return = array('id' => $result['user_id'],
          'username' => $result['username'],
          'token' => $token,
          'firstname' => $result['firstname'],
          'lastname' => $result['lastname'],
          'fbData'=>$fbData,
          'session' => $session);

          //						$this->forward('facebook', 'register', array('fbData'=>$fbData, 'session' => $session, 'shared_content' => $shared_content));
          } else {

          $this->setViewChange('no_account');

          $page_login_trouble = Model_Pages::getPage( JO_Registry::get('page_login_trouble') );
          if($page_login_trouble) {
          $return = array('error' => 13, 'description' => $this->translate($page_login_trouble['title']));
          /*
          $this->view->page_login_trouble = array(
          'title' => $page_login_trouble['title'],
          'href' => WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_login_trouble['page_id'] )
          );
         * 
         *//*
          }

          }
          //}

          }

          } else {
          $this->setViewChange('error_login');

          $page_login_trouble = Model_Pages::getPage( JO_Registry::get('page_login_trouble') );
          if($page_login_trouble) {
          $return = array('error' => 14, 'description' => $this->translate($page_login_trouble['title']));
          /*$this->view->page_login_trouble = array(
          'title' => $page_login_trouble['title'],
          'href' => WM_Router::create( $request->getBaseUrl() . '?controller=pages&action=read&page_id=' . $page_login_trouble['page_id'] )
          );
         * *//*
          }
          }
         */

        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json; charset=utf-8');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    public function logintwAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        $return = array();


        if (isset($_POST['token']))
        {
            $id = $_POST['userId'];

            $user_data = WM_Users::checkLoginFacebookTwitter($id, 'twitter');
            if ($user_data)
            {
                JO_Session::set(array('user' => $user_data));
                JO_Session::clear('user_info_twitteroauth');
                JO_Session::clear('access_token_twitteroauth');

                $token = md5($user_data['user_id']);

                $_SESSION['token'] = $token;
                JO_Session::set('token', $token);


                $return = array('id' => $user_data['user_id'],
                    'username' => $user_data['username'],
                    'token' => $token,
                    'firstname' => $user_data['firstname'],
                    'lastname' => $user_data['lastname']);
            } 
            else
            {
                $return = array('error' => 14, 'description' => $this->translate("Error en el login de twitter"));
            }
        }

        //$token = md5(uniqid(rand(), true));
        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json; charset=utf-8');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

    public function agendaAction()
    {

        $this->noViewRenderer(true);

        $request = $this->getRequest();
        $response = $this->getResponse();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $callback = $request->getRequest('callback');
        if (!preg_match('/^([a-z0-9_.]{1,})$/', $callback))
        {
            $callback = false;
        }

        if (isset($_POST['token']) && $_POST['token'] == md5($_POST['userId']))
        {
            $_SESSION['token'] = $_POST['token'];
            JO_Session::set('token', $_POST['token']);
            Model_Users::editAgenda($request->getPost('agenda'));

            $data = Model_Users::followersUsers($_POST['userId']);
            if ($data)
            {
                foreach ($data AS $key => $user)
                {
                    //add history
                    Model_History::addHistory($user["user_id"], Model_History::COMMENTUSER, $request->getPost('agenda'));
                }
            }

            $return = array('agenda' => $request->getPost('agenda'));
        } else
        {
            //no existe la sesión / no existe el dato recibido por post / el token no es igual.
            $return = array('error' => 401, 'description' => $this->translate('wrong token'));
        }

        if ($callback)
        {
            $return = $callback . '(' . JO_Json::encode($return) . ')';
        } else
        {
            $response->addHeader('Cache-Control: no-cache, must-revalidate');
            $response->addHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $response->addHeader('Content-type: application/json');
            $return = JO_Json::encode($return);
        }

        $response->appendBody($return);
    }

}

?>
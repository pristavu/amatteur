<?php

class SearchController extends JO_Action
{

    private function searchMenu($query)
    {
        $request = $this->getRequest();

        return array(
            array(
                'title' => $this->translate('Amatteur'),
                'active' => in_array($request->getAction(), array('index', 'page', 'view')),
                'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=amatteur&q=' . $query)
            ),
            array(
                'title' => $this->translate('Deportistas Activados'),
                'active' => in_array($request->getAction(), array('boards')),
                'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=activate&q=' . $query)
            ),
            array(
                'title' => $this->translate('Servicios y Negocios'),
                'active' => in_array($request->getAction(), array('people')),
                'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=services&q=' . $query)
            ),
        );
    }

    /*
      private function searchMenu($query) {
      $request = $this->getRequest();
      return array(
      array(
      'title' => $this->translate('Amatteur'),
      'active' => in_array($request->getAction(), array('amatteur')),
      'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&q=' . $query)
      ),
      array(
      'title' => $this->translate('Activate'),
      'active' => in_array($request->getAction(), array('activate')),
      'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&action=activate&q=' . $query)
      ),
      array(
      'title' => $this->translate('Services'),
      'active' => in_array($request->getAction(), array('services')),
      'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&action=services&q=' . $query)
      ),
      );
      }
     */

    
    public function advancedAction()
    {
        $request = $this->getRequest();



        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $this->view->loged = JO_Session::get('user[user_id]');

        //////////// Categories ////////////
        $this->view->categories = array();
        $categories = Model_Categories::getCategories(array(
                    'filter_status' => 1
                ));

        foreach ($categories as $category)
        {
            $category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
            $this->view->categories[] = $category;
        }


        //////////// User Type ////////////
        $this->view->user_types = array();
        $user_types = Model_Users::getUserType(array(
                    'filter_status' => 1
                ));

        //////////// Age ////////////
        $this->view->ages = array();
        $ages = Model_Users::getAge();
        $this->view->ages = $ages;

        //////////// Level ////////////
        $this->view->levels = array();
        $levels = Model_Users::getLevel();
        $this->view->levels = $levels;


        if ($request->issetRequest('firstname'))
        {
            $this->view->firstname = $request->getRequest('firstname');
        } else
        {
            $this->view->firstname = '';
        }

        if ($request->issetRequest('words'))
        {
            $this->view->words = $request->getRequest('words');
        } else
        {
            $this->view->words = '';
        }

        $this->view->cat_title1 = '';
        $this->view->sport_category_1 = '';
        if ($request->issetRequest('sport_category_1'))
        {
            $this->view->sport_category_1 = $request->getRequest('sport_category_1');
            if ($request->getRequest('sport_category_1') != "")
            {
                if ($request->getRequest('sport_category_1') == 1)
                {
                    $this->view->cat_title1 = "Todo";
                } else
                {
                    $this->view->cat_title1 = Model_Boards::getCategoryTitle($request->getRequest('sport_category_1'));
                }
            }
        } else
        {
            $this->view->sport_category_1 = '';
        }
        $this->view->cat_title2 = '';
        $this->view->sport_category_2 = '';
        if ($request->issetRequest('sport_category_2'))
        {
            $this->view->sport_category_2 = $request->getRequest('sport_category_2');
            if ($request->getRequest('sport_category_2') != "")
            {
                if ($request->getRequest('sport_category_2') == 1)
                {
                    $this->view->cat_title2 = "Todo";
                } else
                {
                    $this->view->cat_title2 = Model_Boards::getCategoryTitle($request->getRequest('sport_category_2'));
                }
            }
        } else
        {
            $this->view->sport_category_2 = '';
        }
        $this->view->cat_title3 = '';
        $this->view->sport_category_3 = '';
        if ($request->issetRequest('sport_category_3'))
        {
            $this->view->sport_category_3 = $request->getRequest('sport_category_3');
            if ($request->getRequest('sport_category_3') != "")
            {
                if ($request->getRequest('sport_category_3') == 1)
                {
                    $this->view->cat_title3 = "Todo";
                } else
                {
                    $this->view->cat_title3 = Model_Boards::getCategoryTitle($request->getRequest('sport_category_3'));
                }
            }
        } else
        {
            $this->view->sport_category_3 = '';
        }
        $this->view->usertype_title = '';
        $this->view->type_user = '';
        if ($request->issetRequest('type_user'))
        {
            $this->view->type_user = $request->getRequest('type_user');
            if ($request->getRequest('type_user') != "")
            {
                $this->view->usertype_title = Model_Users::getUserTypeTitle($request->getRequest('type_user'));
            }
        } else
        {
            $this->view->type_user = '';
        }

        //gender
        if ($request->issetRequest('gender'))
        {
            $this->view->gender = $request->getRequest('gender');
        } elseif (isset($user_data['gender']))
        {
            $this->view->gender = $user_data['gender'];
        } else
        {
            $this->view->gender = "";
        }

        //location		
        if ($request->issetRequest('location'))
        {
            $this->view->location = $request->getRequest('location');
        } elseif (isset($user_data['location']))
        {
            $this->view->location = $user_data['location'];
        } else
        {
            $this->view->location = '';
        }

        //sport category
        if ($request->issetRequest('sport_category'))
        {
            $this->view->sport_category = $request->getRequest('sport_category');
            if ($request->getRequest('sport_category') != "")
            {
                $this->view->cat_title = Model_Boards::getCategoryTitle($request->getRequest('sport_category'));
            }
        } elseif (isset($user_data['sport_category']))
        {
            $this->view->sport_category = $user_data['sport_category'];
            $this->view->cat_title = Model_Boards::getCategoryTitle($user_data['sport_category']);
        } else
        {
            $this->view->cat_title = '';
            $this->view->sport_category = '';
        }

        //age
        if ($request->issetRequest('age'))
        {
            $this->view->age = $request->getRequest('age');
            if ($request->getRequest('age') != "")
            {
                $this->view->age_title = Model_Users::getAgeTitle($request->getRequest('age'));
            }
        } elseif (isset($user_data['age']))
        {
            $this->view->age = $user_data['age'];
            $this->view->age_title = Model_Users::getAgeTitle($user_data['age']);
        } else
        {
            $this->view->age_title = '';
            $this->view->age = '';
        }

        //level
        if ($request->issetRequest('level'))
        {
            $this->view->level = $request->getRequest('level');
            if ($request->getRequest('level') != "")
            {
                $this->view->level_title = Model_Users::getLevelTitle($request->getRequest('level'));
            }
        } elseif (isset($user_data['level']))
        {
            $this->view->level = $user_data['level'];
            $this->view->level_title = Model_Users::getLevelTitle($user_data['level']);
        } else
        {
            $this->view->level_title = '';
            $this->view->level = '';
        }

        //option1		
        if ($request->issetRequest('option1'))
        {
            $this->view->option1 = $request->getRequest('option1');
        } elseif (isset($user_data['option1']))
        {
            $this->view->option1 = $user_data['option1'];
        } else
        {
            $this->view->option1 = '';
        }

        //option2		
        if ($request->issetRequest('option2'))
        {
            $this->view->option2 = $request->getRequest('option2');
        } elseif (isset($user_data['option2']))
        {
            $this->view->option2 = $user_data['option2'];
        } else
        {
            $this->view->option2 = '';
        }

        //option3
        if ($request->issetRequest('option3'))
        {
            $this->view->option3 = $request->getRequest('option3');
        } elseif (isset($user_data['option3']))
        {
            $this->view->option3 = $user_data['option3'];
        } else
        {
            $this->view->option3 = '';
        }

        //option4		
        if ($request->issetRequest('option4'))
        {
            $this->view->option4 = $request->getRequest('option4');
        } elseif (isset($user_data['option4']))
        {
            $this->view->option4 = $user_data['option4'];
        } else
        {
            $this->view->option4 = '';
        }

        //option5		
        if ($request->issetRequest('option5'))
        {
            $this->view->option5 = $request->getRequest('option5');
        } elseif (isset($user_data['option5']))
        {
            $this->view->option5 = $user_data['option5'];
        } else
        {
            $this->view->option5 = '';
        }

        //option6		
        if ($request->issetRequest('option6'))
        {
            $this->view->option6 = $request->getRequest('option6');
        } elseif (isset($user_data['option6']))
        {
            $this->view->option6 = $user_data['option6'];
        } else
        {
            $this->view->option6 = '';
        }

        //option7		
        if ($request->issetRequest('option7'))
        {
            $this->view->option7 = $request->getRequest('option7');
        } elseif (isset($user_data['option7']))
        {
            $this->view->option7 = $user_data['option7'];
        } else
        {
            $this->view->option7 = '';
        }

        //option8		
        if ($request->issetRequest('option8'))
        {
            $this->view->option8 = $request->getRequest('option8');
        } elseif (isset($user_data['option8']))
        {
            $this->view->option8 = $user_data['option8'];
        } else
        {
            $this->view->option8 = '';
        }

        //option9		
        if ($request->issetRequest('option9'))
        {
            $this->view->option9 = $request->getRequest('option9');
        } elseif (isset($user_data['option9']))
        {
            $this->view->option9 = $user_data['option9'];
        } else
        {
            $this->view->option9 = '';
        }

        //option10		
        if ($request->issetRequest('option10'))
        {
            $this->view->option10 = $request->getRequest('option10');
        } elseif (isset($user_data['option10']))
        {
            $this->view->option10 = $user_data['option10'];
        } else
        {
            $this->view->option10 = '';
        }

        //option11		
        if ($request->issetRequest('option11'))
        {
            $this->view->option11 = $request->getRequest('option11');
        } elseif (isset($user_data['option11']))
        {
            $this->view->option11 = $user_data['option11'];
        } else
        {
            $this->view->option11 = '';
        }

        //option12		
        if ($request->issetRequest('option12'))
        {
            $this->view->option12 = $request->getRequest('option12');
        } elseif (isset($user_data['option12']))
        {
            $this->view->option12 = $user_data['option12'];
        } else
        {
            $this->view->option12 = '';
        }

        //option13
        if ($request->issetRequest('option13'))
        {
            $this->view->option13 = $request->getRequest('option13');
        } elseif (isset($user_data['option13']))
        {
            $this->view->option13 = $user_data['option13'];
        } else
        {
            $this->view->option13 = '';
        }

        //option14		
        if ($request->issetRequest('option14'))
        {
            $this->view->option14 = $request->getRequest('option14');
        } elseif (isset($user_data['option14']))
        {
            $this->view->option14 = $user_data['option14'];
        } else
        {
            $this->view->option14 = '';
        }

        //option15		
        if ($request->issetRequest('option15'))
        {
            $this->view->option15 = $request->getRequest('option15');
        } elseif (isset($user_data['option15']))
        {
            $this->view->option15 = $user_data['option15'];
        } else
        {
            $this->view->option15 = '';
        }

        //option16		
        if ($request->issetRequest('option16'))
        {
            $this->view->option16 = $request->getRequest('option16');
        } elseif (isset($user_data['option16']))
        {
            $this->view->option16 = $user_data['option16'];
        } else
        {
            $this->view->option16 = '';
        }

        //option17		
        if ($request->issetRequest('option17'))
        {
            $this->view->option17 = $request->getRequest('option17');
        } elseif (isset($user_data['option17']))
        {
            $this->view->option17 = $user_data['option17'];
        } else
        {
            $this->view->option17 = '';
        }

        //option18		
        if ($request->issetRequest('option18'))
        {
            $this->view->option18 = $request->getRequest('option18');
        } elseif (isset($user_data['option18']))
        {
            $this->view->option18 = $user_data['option18'];
        } else
        {
            $this->view->option18 = '';
        }        
        
        $this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        //controlador
        $id = $request->getRequest('id');
        if ($id == "amatteur")
        {
            //$this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=amatteur');
            $this->view->advancedActive = "amatteur";
            $with_action = $request->getAction();
            $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        } else if ($id == "activate")
        {
            //$this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=activate');                    
            $this->view->advancedActive = "activate";
            $with_action = $request->getAction();
            $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        } else if ($id == "services")
        {
            //$this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=services');                    
            $this->view->advancedActive = "services";
            $with_action = $request->getAction();
            $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        } else if ($id == "voluntarios")
        {
            //$this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=voluntarios');                    
            $this->view->advancedActive = "voluntarios";
            $with_action = $request->getAction();
            $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        }

        
        if ($request->issetRequest('zoom'))
        {
            $this->view->isPost = "true";
            //if($request->issetRequest('words'))
            {

                $query = $request->getRequest('words');

                $this->view->query = $query;

                //$this->view->menuSearch = $this->searchMenu($query);

                $this->view->pins = '';
                
                if ($id == "amatteur")
                {
                    $validate = new Helper_Validate();

                    if($request->getRequest('option1') == "" && $request->getRequest('option2') == "" && $request->getRequest('option3') == "" && $request->getRequest('option4') == "" && $request->getRequest('option5') == "" && $request->getRequest('option6') == "" && $request->getRequest('option7') == "") {
                        $validate->_set_rules($request->getRequest('option1'), $this->translate('Opción de búsqueda amatteur'), 'not_empty;min_length[3];max_length[100]');

                    }
                    if($validate->_valid_form()) 
                    {
                        //boards
                        if ($request->getRequest('option1') == "1")
                        {
                            $data = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_title' => $query,
                                'filter_category_id' => $request->getRequest('sport_category_1')
                            );


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


                                    $view->board = $board;
                                    $this->view->pins .= $view->render('box', 'boards');
                                }
                            }
                        }
                        //users
                        if ($request->getRequest('option2') == "1")
                        {
                            $data = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_username' => $query,
                                'filter_firstname' => $request->getRequest('firstname'),
                                'filter_location' => $request->getRequest('location'),
                                'filter_gender' => $request->getRequest('gender'),
                                'filter_sport_category_1' => $request->getRequest('sport_category_1')
                            );


                            $users = Model_Users::getUsers($data);
                            if ($request->issetRequest('location'))
                            {
                                $users_id = Model_Users::getLocationUsers($request->getRequest('location'));
                                foreach ($users_id as $user_id)
                                {
                                    $dataUser = array(
                                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                        'limit' => JO_Registry::get('config_front_limit'),
                                        'filter_user_id' => $user_id["user_id"]
                                    );
                                    $userAux = Model_Users::getUsers($dataUser);
                                    //$users[] = Model_Users::getUsers($dataUser);
                                    if ($userAux)
                                    {
                                        //array_push($users, $userAux);
                                        $users[] = $userAux[0];
                                    }
                                }
                            }
                            if ($request->issetRequest('sport_category_1'))
                            {
                                $users_id = Model_Users::getUserIdSportsUser($request->getRequest('sport_category_1'));
                                foreach ($users_id as $user_id)
                                {
                                    $dataUser = array(
                                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                        'limit' => JO_Registry::get('config_front_limit'),
                                        'filter_user_id' => $user_id["user_id"]
                                    );
                                    $userAux = Model_Users::getUsers($dataUser);
                                    if ($userAux)
                                    {
                                        $users[] = $userAux[0];
                                    }
                                }
                            }

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

                                    $view->key = $key % 2 == 0;
                                    $view->user = $user;
                                    $this->view->pins .= $view->render('boxSearch', 'users');
                                }
                                $this->view->users = $users;
                            }
                        }
                        //imagenes
                        if ($request->getRequest('option3') == "1")
                        {
                            if ($query != "")
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_image' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_description' => $query,
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                            else
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_image' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                                

                            $pins = Model_Pins::getPins($data);
                            if ($pins)
                            {
                                foreach ($pins AS $pin)
                                {
                                    $this->view->pins .= Helper_Pin::returnHtml($pin);
                                }
                                // 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
                            }
                        }
                        //videos
                        if ($request->getRequest('option4') == "1")
                        {
                            if ($query != "")
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_video' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_description' => $query,
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                            else
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_video' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }

                            $pins = Model_Pins::getPins($data);
                            if ($pins)
                            {
                                foreach ($pins AS $pin)
                                {
                                    $this->view->pins .= Helper_Pin::returnHtml($pin);
                                }
                                // 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
                            }
                        }
                        //gifts
                        if ($request->getRequest('option5') == "1")
                        {
                            if ($query != "")
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'allow_gifts' => true,
                                    'filter_search' => 1,                                    
                                    'filter_description' => $query,
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                            else
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'allow_gifts' => true,
                                    'filter_search' => 1,                                    
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }

                            $pins = Model_Pins::getPins($data);
                            if ($pins)
                            {
                                foreach ($pins AS $pin)
                                {
                                    $this->view->pins .= Helper_Pin::returnHtml($pin);
                                }
                                // 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
                            }
                        }
                        //articles
                        if ($request->getRequest('option6') == "1")
                        {
                            if ($query != "")
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_article' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_description' => $query,
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                            else
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_article' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );

                            }

                            $pins = Model_Pins::getPins($data);
                            if ($pins)
                            {
                                foreach ($pins AS $pin)
                                {
                                    $this->view->pins .= Helper_Pin::returnHtml($pin);
                                }
                                // 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
                            }
                        }
                    } else {
                            $this->view->error = $this->translate("Tiene que seleccionar algún criterio de búsqueda");//$validate->_get_error_messages();
                    }
                    
                }
                else if ($id == "activate")
                {
                    $dataActivate = array(
                        /*
                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                        'limit' => JO_Registry::get('config_front_limit'),
                         */
                        'start' => ( 1000 * $page ) - 1000,
                        'limit' => 1000,
                        'filter_gender' => $request->getRequest('gender'),
                        'filter_age' => $request->getRequest('age'),
                        'filter_location' => $request->getRequest('location'),
                        'filter_sport_category' => $request->getRequest('sport_category'),
                        'filter_level' => $request->getRequest('level'),
                        'filter_option1' => $request->getRequest('option1'),
                        'filter_option2' => $request->getRequest('option2'),
                        'filter_option3' => $request->getRequest('option3'),
                        'filter_option4' => $request->getRequest('option4'),
                        'filter_option5' => $request->getRequest('option5'),
                        'filter_option6' => $request->getRequest('option6'),
                        'filter_option7' => $request->getRequest('option7'),
                        'filter_option8' => $request->getRequest('option8')
                    );

                    $activate = Model_Users::getUsersActivate($dataActivate);

                    if ($activate)
                    {
                        $usersTot = array();
                        foreach ($activate as $user_id)
                        {

                            $data = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_user_id' => $user_id["user_id"]
                            );

                            $users = Model_Users::getUsers($data);
                            if ($users)
                            {
                                $href = "";
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
                                    $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
                                    $user['follow'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id']);

                                    $view->key = $key % 2 == 0;
                                    $user["location"] = $user_id["location"];
                                    $user["sport_category_1"] = Model_Boards::getCategoryTitle($user_id["sport_category"]);
                                    $user["level"] = Model_Users::getLevelTitle($user_id["level"]);
                                    $user["option1"] = $user_id["option1"];
                                    $user["option2"] = $user_id["option2"];
                                    $user["option3"] = $user_id["option3"];
                                    $user["option4"] = $user_id["option4"];
                                    $user["option5"] = $user_id["option5"];
                                    $user["option6"] = $user_id["option6"];
                                    $user["option7"] = $user_id["option7"];
                                    $user["option8"] = $user_id["option8"];
                                    $user["comment"] = $user_id["comment"];
                                    $view->user = $user;
                                    $this->view->addMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=create');                                                                                        
                                    $this->view->stateMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=state');
                                    $this->view->viewMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=view');
                                    $this->view->pins .= $view->render('boxActivate', 'users');
                                }
                                $users[0]["location"] = $user_id["location"];
                                $users[0]["lat"] = $user_id["lat"];
                                $users[0]["len"] = $user_id["len"];
                                $users[0]["href"] = $href;
                                $usersTot[] = $users[0];
                            }
                        }
                        $this->view->users = $usersTot;
                        $this->view->class_contaner = 'persons';
                    }
                } 
                else if ($id == "services")
                {
                    $type_user = array(
                        'filter_option1' => $request->getRequest('option1'),
                        'filter_option2' => $request->getRequest('option2'),
                        //'filter_option3' => $request->getRequest('option3'),
                        'filter_option4' => $request->getRequest('option4'),
                        'filter_option5' => $request->getRequest('option5'),
                        'filter_option6' => $request->getRequest('option6'),
                        'filter_option7' => $request->getRequest('option7'),
                        'filter_option8' => $request->getRequest('option8'),
                        'filter_option9' => $request->getRequest('option9'),
                        'filter_option10' => $request->getRequest('option10')
                    );

                    $data = array(
                        /*
                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                        'limit' => JO_Registry::get('config_front_limit'),
                         */
                        'start' => ( 1000 * $page ) - 1000,
                        'limit' => 1000,
                        'filter_username' => $request->getRequest('words'),
                        'filter_sport_category' => $request->getRequest('sport_category_1'),
                        'filter_firstname' => $request->getRequest('firstname'),
                        'filter_typeuser' => $type_user,
                        'filter_typeuser_profesional' => 1,
                        'filter_location' => $request->getRequest('location')
                    );


                    $users = Model_Users::getUsers($data);
                    if ($request->issetRequest('location'))
                    {
                        if ($request->getRequest('location') != "")
                        {
                            $users_id = Model_Users::getLocationUsers($request->getRequest('location'), 1);
                            foreach ($users_id as $user_id)
                            {
                                $dataUser = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_user_id' => $user_id["user_id"]
                                );
                                $userAux = Model_Users::getUsers($dataUser);
                                //$users[] = Model_Users::getUsers($dataUser);
                                if ($userAux)
                                {
                                    $userAux[0]["location"] = $user_id["location"];
                                    $userAux[0]["lat"] = $user_id["lat"];
                                    $userAux[0]["len"] = $user_id["len"];
                                    //array_push($users, $userAux);
                                    $users[] = $userAux[0];

                                }
                            }
                        }
                    }
                    if ($request->issetRequest('sport_category_1'))
                    {
                        $users_id = Model_Users::getUserIdSportsUser($request->getRequest('sport_category_1'));
                        foreach ($users_id as $user_id)
                        {
                            $dataUser = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_user_id' => $user_id["user_id"]
                            );
                            $userAux = Model_Users::getUsers($dataUser);
                            if ($userAux)
                            {
                                $users[] = $userAux[0];
                            }
                        }
                    }
                    if ($users)
                    {
                        $href = "";
                        $i = 0;
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
                            $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
                            $user['follow'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id']);

                            $view->key = $key % 2 == 0;
                            $view->services = true;
                            $view->user = $user;
                            $this->view->pins .= $view->render('boxSearch', 'users');
                            $users[$i]["href"] = $href;
                            $i++;
                        }
                        $this->view->users = $users;                        
                    }
                }
                else if ($id == "voluntarios")
                {
                    $dataVoluntarios = array(
                        /*
                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                        'limit' => JO_Registry::get('config_front_limit'),
                         */
                        'start' => ( 1000 * $page ) - 1000,
                        'limit' => 1000,
                        'filter_location' => $request->getRequest('location'),
                        'filter_sport_category' => $request->getRequest('sport_category'),
                        'filter_option1' => $request->getRequest('option1'),
                        'filter_option2' => $request->getRequest('option2'),
                        'filter_option3' => $request->getRequest('option3'),
                        'filter_option4' => $request->getRequest('option4'),
                        'filter_option5' => $request->getRequest('option5'),
                        'filter_option6' => $request->getRequest('option6'),
                        'filter_option7' => $request->getRequest('option7'),
                        'filter_option8' => $request->getRequest('option8'),
                        'filter_option9' => $request->getRequest('option9'),
                        'filter_option10' => $request->getRequest('option10'),                        
                        'filter_option11' => $request->getRequest('option11'),
                        'filter_option12' => $request->getRequest('option12'),
                        'filter_option13' => $request->getRequest('option13'),
                        'filter_option14' => $request->getRequest('option14'),
                        'filter_option15' => $request->getRequest('option15'),
                        'filter_option16' => $request->getRequest('option16'),
                        'filter_option17' => $request->getRequest('option17'),
                        'filter_option18' => $request->getRequest('option18')
                        
                    );

                    $voluntario = Model_Users::getUsersVoluntarios($dataVoluntarios);

                    if ($voluntario)
                    {
                        $usersTot = array();
                        foreach ($voluntario as $voluntario_user_id)
                        {

                            $data = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_user_id' => $voluntario_user_id["user_id"]
                            );

                            $users = Model_Users::getUsers($data);
                            if ($users)
                            {
                                $href = "";
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
                                    $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
                                    $user['follow'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id']);

                                    $view->key = $key % 2 == 0;
                                    $user["location"] = $voluntario_user_id["location"];
                                    $user["sport_category"] = Model_Boards::getCategoryTitle($voluntario_user_id["sport_category"]);
                                    $user["option1"] = $voluntario_user_id["option1"];
                                    $user["option2"] = $voluntario_user_id["option2"];
                                    $user["option3"] = $voluntario_user_id["option3"];
                                    $user["option4"] = $voluntario_user_id["option4"];
                                    $user["option5"] = $voluntario_user_id["option5"];
                                    $user["option6"] = $voluntario_user_id["option6"];
                                    $user["option7"] = $voluntario_user_id["option7"];
                                    $user["option8"] = $voluntario_user_id["option8"];
                                    $user["option9"] = $voluntario_user_id["option9"];                                    
                                    $user["option10"] = $voluntario_user_id["option10"];
                                    $user["option11"] = $voluntario_user_id["option11"];
                                    $user["option12"] = $voluntario_user_id["option12"];
                                    $user["option13"] = $voluntario_user_id["option13"];
                                    $user["option14"] = $voluntario_user_id["option14"];
                                    $user["option15"] = $voluntario_user_id["option15"];
                                    $user["option16"] = $voluntario_user_id["option16"];
                                    $user["option17"] = $voluntario_user_id["option17"];
                                    $user["option18"] = $voluntario_user_id["option18"];
                                    $user["comment"] = $voluntario_user_id["comment"];
                                    $view->user = $user;
                                    $this->view->addMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=create');                                                                                        
                                    $this->view->stateMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=state');
                                    $this->view->viewMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=view');
                                    $this->view->pins .= $view->render('boxVoluntarios', 'users');
                                }
                                $users[0]["location"] = $voluntario_user_id["location"];
                                $users[0]["lat"] = $voluntario_user_id["lat"];
                                $users[0]["len"] = $voluntario_user_id["len"];
                                $users[0]["href"] = $href;
                                $usersTot[] = $users[0];
                            }
                        }
                        $this->view->users = $usersTot;
                        $this->view->class_contaner = 'persons';
                    }
                }
            }
            if ($this->view->isPost == "true" && !$this->view->pins && !$this->view->error)
            {
                $this->view->error = $this->translate("La búsqueda no ha devuelto resultados");
            }
            
        }

        if ($request->isXmlHttpRequest())
        {
            echo $this->view->pins;
            $this->noViewRenderer(true);
        } else
        {
            $this->view->children = array(
                'header_part' => 'layout/header_part',
                'footer_part' => 'layout/footer_part'
            );
        }


    }


    public function oldadvancedAction()
    {
        $request = $this->getRequest();



        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $this->view->loged = JO_Session::get('user[user_id]');

        //////////// Categories ////////////
        $this->view->categories = array();
        $categories = Model_Categories::getCategories(array(
                    'filter_status' => 1
                ));

        foreach ($categories as $category)
        {
            $category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
            $this->view->categories[] = $category;
        }


        //////////// User Type ////////////
        $this->view->user_types = array();
        $user_types = Model_Users::getUserType(array(
                    'filter_status' => 1
                ));

        //////////// Age ////////////
        $this->view->ages = array();
        $ages = Model_Users::getAge();
        $this->view->ages = $ages;

        //////////// Level ////////////
        $this->view->levels = array();
        $levels = Model_Users::getLevel();
        $this->view->levels = $levels;


        if ($request->issetPost('firstname'))
        {
            $this->view->firstname = $request->getPost('firstname');
        } else
        {
            $this->view->firstname = '';
        }

        if ($request->issetPost('words'))
        {
            $this->view->words = $request->getPost('words');
        } else
        {
            $this->view->words = '';
        }

        $this->view->cat_title1 = '';
        $this->view->sport_category_1 = '';
        if ($request->issetPost('sport_category_1'))
        {
            $this->view->sport_category_1 = $request->getPost('sport_category_1');
            if ($request->getPost('sport_category_1') != "")
            {
                if ($request->getPost('sport_category_1') == 1)
                {
                    $this->view->cat_title1 = "Todo";
                } else
                {
                    $this->view->cat_title1 = Model_Boards::getCategoryTitle($request->getPost('sport_category_1'));
                }
            }
        } else
        {
            $this->view->sport_category_1 = '';
        }
        $this->view->cat_title2 = '';
        $this->view->sport_category_2 = '';
        if ($request->issetPost('sport_category_2'))
        {
            $this->view->sport_category_2 = $request->getPost('sport_category_2');
            if ($request->getPost('sport_category_2') != "")
            {
                if ($request->getPost('sport_category_2') == 1)
                {
                    $this->view->cat_title2 = "Todo";
                } else
                {
                    $this->view->cat_title2 = Model_Boards::getCategoryTitle($request->getPost('sport_category_2'));
                }
            }
        } else
        {
            $this->view->sport_category_2 = '';
        }
        $this->view->cat_title3 = '';
        $this->view->sport_category_3 = '';
        if ($request->issetPost('sport_category_3'))
        {
            $this->view->sport_category_3 = $request->getPost('sport_category_3');
            if ($request->getPost('sport_category_3') != "")
            {
                if ($request->getPost('sport_category_3') == 1)
                {
                    $this->view->cat_title3 = "Todo";
                } else
                {
                    $this->view->cat_title3 = Model_Boards::getCategoryTitle($request->getPost('sport_category_3'));
                }
            }
        } else
        {
            $this->view->sport_category_3 = '';
        }
        $this->view->usertype_title = '';
        $this->view->type_user = '';
        if ($request->issetPost('type_user'))
        {
            $this->view->type_user = $request->getPost('type_user');
            if ($request->getPost('type_user') != "")
            {
                $this->view->usertype_title = Model_Users::getUserTypeTitle($request->getPost('type_user'));
            }
        } else
        {
            $this->view->type_user = '';
        }

        //gender
        if ($request->issetPost('gender'))
        {
            $this->view->gender = $request->getRequest('gender');
        } elseif (isset($user_data['gender']))
        {
            $this->view->gender = $user_data['gender'];
        } else
        {
            $this->view->gender = "";
        }

        //location		
        if ($request->issetPost('location'))
        {
            $this->view->location = $request->getPost('location');
        } elseif (isset($user_data['location']))
        {
            $this->view->location = $user_data['location'];
        } else
        {
            $this->view->location = '';
        }

        //sport category
        if ($request->issetPost('sport_category'))
        {
            $this->view->sport_category = $request->getPost('sport_category');
            if ($request->getPost('sport_category') != "")
            {
                $this->view->cat_title = Model_Boards::getCategoryTitle($request->getPost('sport_category'));
            }
        } elseif (isset($user_data['sport_category']))
        {
            $this->view->sport_category = $user_data['sport_category'];
            $this->view->cat_title = Model_Boards::getCategoryTitle($user_data['sport_category']);
        } else
        {
            $this->view->cat_title = '';
            $this->view->sport_category = '';
        }

        //age
        if ($request->issetPost('age'))
        {
            $this->view->age = $request->getPost('age');
            if ($request->getPost('age') != "")
            {
                $this->view->age_title = Model_Users::getAgeTitle($request->getPost('age'));
            }
        } elseif (isset($user_data['age']))
        {
            $this->view->age = $user_data['age'];
            $this->view->age_title = Model_Users::getAgeTitle($user_data['age']);
        } else
        {
            $this->view->age_title = '';
            $this->view->age = '';
        }

        //level
        if ($request->issetPost('level'))
        {
            $this->view->level = $request->getPost('level');
            if ($request->getPost('level') != "")
            {
                $this->view->level_title = Model_Users::getLevelTitle($request->getPost('level'));
            }
        } elseif (isset($user_data['level']))
        {
            $this->view->level = $user_data['level'];
            $this->view->level_title = Model_Users::getLevelTitle($user_data['level']);
        } else
        {
            $this->view->level_title = '';
            $this->view->level = '';
        }

        //option1		
        if ($request->issetPost('option1'))
        {
            $this->view->option1 = $request->getPost('option1');
        } elseif (isset($user_data['option1']))
        {
            $this->view->option1 = $user_data['option1'];
        } else
        {
            $this->view->option1 = '';
        }

        //option2		
        if ($request->issetPost('option2'))
        {
            $this->view->option2 = $request->getPost('option2');
        } elseif (isset($user_data['option2']))
        {
            $this->view->option2 = $user_data['option2'];
        } else
        {
            $this->view->option2 = '';
        }

        //option3
        if ($request->issetPost('option3'))
        {
            $this->view->option3 = $request->getPost('option3');
        } elseif (isset($user_data['option3']))
        {
            $this->view->option3 = $user_data['option3'];
        } else
        {
            $this->view->option3 = '';
        }

        //option4		
        if ($request->issetPost('option4'))
        {
            $this->view->option4 = $request->getPost('option4');
        } elseif (isset($user_data['option4']))
        {
            $this->view->option4 = $user_data['option4'];
        } else
        {
            $this->view->option4 = '';
        }

        //option5		
        if ($request->issetPost('option5'))
        {
            $this->view->option5 = $request->getPost('option5');
        } elseif (isset($user_data['option5']))
        {
            $this->view->option5 = $user_data['option5'];
        } else
        {
            $this->view->option5 = '';
        }

        //option6		
        if ($request->issetPost('option6'))
        {
            $this->view->option6 = $request->getPost('option6');
        } elseif (isset($user_data['option6']))
        {
            $this->view->option6 = $user_data['option6'];
        } else
        {
            $this->view->option6 = '';
        }

        //option7		
        if ($request->issetPost('option7'))
        {
            $this->view->option7 = $request->getPost('option7');
        } elseif (isset($user_data['option7']))
        {
            $this->view->option7 = $user_data['option7'];
        } else
        {
            $this->view->option7 = '';
        }

        //option8		
        if ($request->issetPost('option8'))
        {
            $this->view->option8 = $request->getPost('option8');
        } elseif (isset($user_data['option8']))
        {
            $this->view->option8 = $user_data['option8'];
        } else
        {
            $this->view->option8 = '';
        }

        //option9		
        if ($request->issetPost('option9'))
        {
            $this->view->option9 = $request->getPost('option9');
        } elseif (isset($user_data['option9']))
        {
            $this->view->option9 = $user_data['option9'];
        } else
        {
            $this->view->option9 = '';
        }

        //option10		
        if ($request->issetPost('option10'))
        {
            $this->view->option10 = $request->getPost('option10');
        } elseif (isset($user_data['option10']))
        {
            $this->view->option10 = $user_data['option10'];
        } else
        {
            $this->view->option10 = '';
        }

        //option11		
        if ($request->issetPost('option11'))
        {
            $this->view->option11 = $request->getPost('option11');
        } elseif (isset($user_data['option11']))
        {
            $this->view->option11 = $user_data['option11'];
        } else
        {
            $this->view->option11 = '';
        }

        //option12		
        if ($request->issetPost('option12'))
        {
            $this->view->option12 = $request->getPost('option12');
        } elseif (isset($user_data['option12']))
        {
            $this->view->option12 = $user_data['option12'];
        } else
        {
            $this->view->option12 = '';
        }

        //option13
        if ($request->issetPost('option13'))
        {
            $this->view->option13 = $request->getPost('option13');
        } elseif (isset($user_data['option13']))
        {
            $this->view->option13 = $user_data['option13'];
        } else
        {
            $this->view->option13 = '';
        }

        //option14		
        if ($request->issetPost('option14'))
        {
            $this->view->option14 = $request->getPost('option14');
        } elseif (isset($user_data['option14']))
        {
            $this->view->option14 = $user_data['option14'];
        } else
        {
            $this->view->option14 = '';
        }

        //option15		
        if ($request->issetPost('option15'))
        {
            $this->view->option15 = $request->getPost('option15');
        } elseif (isset($user_data['option15']))
        {
            $this->view->option15 = $user_data['option15'];
        } else
        {
            $this->view->option15 = '';
        }

        //option16		
        if ($request->issetPost('option16'))
        {
            $this->view->option16 = $request->getPost('option16');
        } elseif (isset($user_data['option16']))
        {
            $this->view->option16 = $user_data['option16'];
        } else
        {
            $this->view->option16 = '';
        }

        //option17		
        if ($request->issetPost('option17'))
        {
            $this->view->option17 = $request->getPost('option17');
        } elseif (isset($user_data['option17']))
        {
            $this->view->option17 = $user_data['option17'];
        } else
        {
            $this->view->option17 = '';
        }

        //option18		
        if ($request->issetPost('option18'))
        {
            $this->view->option18 = $request->getPost('option18');
        } elseif (isset($user_data['option18']))
        {
            $this->view->option18 = $user_data['option18'];
        } else
        {
            $this->view->option18 = '';
        }        
        
        $this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        //controlador
        $id = $request->getRequest('id');
        if ($id == "amatteur")
        {
            //$this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=amatteur');
            $this->view->advancedActive = "amatteur";
            $with_action = $request->getAction();
            $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        } else if ($id == "activate")
        {
            //$this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=activate');                    
            $this->view->advancedActive = "activate";
            $with_action = $request->getAction();
            $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        } else if ($id == "services")
        {
            //$this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=services');                    
            $this->view->advancedActive = "services";
            $with_action = $request->getAction();
            $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        } else if ($id == "voluntarios")
        {
            //$this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced?id=voluntarios');                    
            $this->view->advancedActive = "voluntarios";
            $with_action = $request->getAction();
            $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        }

        
        if ($request->isPost())
        {
            $this->view->isPost = "true";
            //if($request->issetPost('words'))
            {

                $query = $request->getRequest('words');

                $this->view->query = $query;

                //$this->view->menuSearch = $this->searchMenu($query);

                $this->view->pins = '';
                
                if ($id == "amatteur")
                {
                    $validate = new Helper_Validate();

                    if($request->getRequest('option1') == "" && $request->getRequest('option2') == "" && $request->getRequest('option3') == "" && $request->getRequest('option4') == "" && $request->getRequest('option5') == "" && $request->getRequest('option6') == "" && $request->getRequest('option7') == "") {
                        $validate->_set_rules($request->getRequest('option1'), $this->translate('Opción de búsqueda amatteur'), 'not_empty;min_length[3];max_length[100]');

                    }
                    if($validate->_valid_form()) 
                    {
                        //boards
                        if ($request->getRequest('option1') == "1")
                        {
                            $data = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_title' => $query,
                                'filter_category_id' => $request->getRequest('sport_category_1')
                            );


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


                                    $view->board = $board;
                                    $this->view->pins .= $view->render('box', 'boards');
                                }
                            }
                        }
                        //users
                        if ($request->getRequest('option2') == "1")
                        {
                            $data = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_username' => $query,
                                'filter_firstname' => $request->getRequest('firstname'),
                                'filter_location' => $request->getRequest('location'),
                                'filter_gender' => $request->getRequest('gender'),
                                'filter_sport_category_1' => $request->getRequest('sport_category_1')
                            );


                            $users = Model_Users::getUsers($data);
                            if ($request->issetPost('location'))
                            {
                                $users_id = Model_Users::getLocationUsers($request->getRequest('location'));
                                foreach ($users_id as $user_id)
                                {
                                    $dataUser = array(
                                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                        'limit' => JO_Registry::get('config_front_limit'),
                                        'filter_user_id' => $user_id["user_id"]
                                    );
                                    $userAux = Model_Users::getUsers($dataUser);
                                    //$users[] = Model_Users::getUsers($dataUser);
                                    if ($userAux)
                                    {
                                        //array_push($users, $userAux);
                                        $users[] = $userAux[0];
                                    }
                                }
                            }
                            if ($request->issetPost('sport_category_1'))
                            {
                                $users_id = Model_Users::getUserIdSportsUser($request->getRequest('sport_category_1'));
                                foreach ($users_id as $user_id)
                                {
                                    $dataUser = array(
                                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                        'limit' => JO_Registry::get('config_front_limit'),
                                        'filter_user_id' => $user_id["user_id"]
                                    );
                                    $userAux = Model_Users::getUsers($dataUser);
                                    if ($userAux)
                                    {
                                        $users[] = $userAux[0];
                                    }
                                }
                            }

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

                                    $view->key = $key % 2 == 0;
                                    $view->user = $user;
                                    $this->view->pins .= $view->render('boxSearch', 'users');
                                }
                                $this->view->users = $users;
                            }
                        }
                        //imagenes
                        if ($request->getRequest('option3') == "1")
                        {
                            if ($query != "")
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_image' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_description' => $query,
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                            else
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_image' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                                

                            $pins = Model_Pins::getPins($data);
                            if ($pins)
                            {
                                foreach ($pins AS $pin)
                                {
                                    $this->view->pins .= Helper_Pin::returnHtml($pin);
                                }
                                // 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
                            }
                        }
                        //videos
                        if ($request->getRequest('option4') == "1")
                        {
                            if ($query != "")
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_video' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_description' => $query,
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                            else
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_video' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }

                            $pins = Model_Pins::getPins($data);
                            if ($pins)
                            {
                                foreach ($pins AS $pin)
                                {
                                    $this->view->pins .= Helper_Pin::returnHtml($pin);
                                }
                                // 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
                            }
                        }
                        //gifts
                        if ($request->getRequest('option5') == "1")
                        {
                            if ($query != "")
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'allow_gifts' => true,
                                    'filter_search' => 1,                                    
                                    'filter_description' => $query,
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                            else
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'allow_gifts' => true,
                                    'filter_search' => 1,                                    
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }

                            $pins = Model_Pins::getPins($data);
                            if ($pins)
                            {
                                foreach ($pins AS $pin)
                                {
                                    $this->view->pins .= Helper_Pin::returnHtml($pin);
                                }
                                // 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
                            }
                        }
                        //articles
                        if ($request->getRequest('option6') == "1")
                        {
                            if ($query != "")
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_article' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_description' => $query,
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );
                            }
                            else
                            {
                                $data = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_is_article' => 1,
                                    'filter_search' => 1,                                    
                                    'filter_category_id' => $request->getRequest('sport_category_1') != "" ? $request->getRequest('sport_category_1') : null
                                );

                            }

                            $pins = Model_Pins::getPins($data);
                            if ($pins)
                            {
                                foreach ($pins AS $pin)
                                {
                                    $this->view->pins .= Helper_Pin::returnHtml($pin);
                                }
                                // 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
                            }
                        }
                    } else {
                            $this->view->error = $this->translate("Tiene que seleccionar algún criterio de búsqueda");//$validate->_get_error_messages();
                    }
                    
                }
                else if ($id == "activate")
                {
                    $dataActivate = array(
                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                        'limit' => JO_Registry::get('config_front_limit'),
                        'filter_gender' => $request->getRequest('gender'),
                        'filter_age' => $request->getRequest('age'),
                        'filter_location' => $request->getRequest('location'),
                        'filter_sport_category' => $request->getRequest('sport_category'),
                        'filter_level' => $request->getRequest('level'),
                        'filter_option1' => $request->getRequest('option1'),
                        'filter_option2' => $request->getRequest('option2'),
                        'filter_option3' => $request->getRequest('option3'),
                        'filter_option4' => $request->getRequest('option4'),
                        'filter_option5' => $request->getRequest('option5'),
                        'filter_option6' => $request->getRequest('option6'),
                        'filter_option7' => $request->getRequest('option7'),
                        'filter_option8' => $request->getRequest('option8')
                    );

                    $activate = Model_Users::getUsersActivate($dataActivate);

                    if ($activate)
                    {
                        $usersTot = array();
                        foreach ($activate as $user_id)
                        {

                            $data = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_user_id' => $user_id["user_id"]
                            );

                            $users = Model_Users::getUsers($data);
                            if ($users)
                            {
                                $href = "";
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
                                    $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
                                    $user['follow'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id']);

                                    $view->key = $key % 2 == 0;
                                    $user["location"] = $user_id["location"];
                                    $user["sport_category_1"] = Model_Boards::getCategoryTitle($user_id["sport_category"]);
                                    $user["level"] = Model_Users::getLevelTitle($user_id["level"]);
                                    $user["option1"] = $user_id["option1"];
                                    $user["option2"] = $user_id["option2"];
                                    $user["option3"] = $user_id["option3"];
                                    $user["option4"] = $user_id["option4"];
                                    $user["option5"] = $user_id["option5"];
                                    $user["option6"] = $user_id["option6"];
                                    $user["option7"] = $user_id["option7"];
                                    $user["option8"] = $user_id["option8"];
                                    $user["comment"] = $user_id["comment"];
                                    $view->user = $user;
                                    $this->view->addMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=create');                                                                                        
                                    $this->view->stateMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=state');
                                    $this->view->viewMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=view');
                                    $this->view->pins .= $view->render('boxActivate', 'users');
                                }
                                $users[0]["location"] = $user_id["location"];
                                $users[0]["lat"] = $user_id["lat"];
                                $users[0]["len"] = $user_id["len"];
                                $users[0]["href"] = $href;
                                $usersTot[] = $users[0];
                            }
                        }
                        $this->view->users = $usersTot;
                        $this->view->class_contaner = 'persons';
                    }
                } 
                else if ($id == "services")
                {
                    $type_user = array(
                        'filter_option1' => $request->getRequest('option1'),
                        'filter_option2' => $request->getRequest('option2'),
                        //'filter_option3' => $request->getRequest('option3'),
                        'filter_option4' => $request->getRequest('option4'),
                        'filter_option5' => $request->getRequest('option5'),
                        'filter_option6' => $request->getRequest('option6'),
                        'filter_option7' => $request->getRequest('option7'),
                        'filter_option8' => $request->getRequest('option8'),
                        'filter_option9' => $request->getRequest('option9'),
                        'filter_option10' => $request->getRequest('option10')
                    );

                    $data = array(
                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                        'limit' => JO_Registry::get('config_front_limit'),
                        'filter_username' => $request->getRequest('words'),
                        'filter_sport_category' => $request->getRequest('sport_category_1'),
                        'filter_firstname' => $request->getRequest('firstname'),
                        'filter_typeuser' => $type_user,
                        'filter_typeuser_profesional' => 1,
                        'filter_location' => $request->getRequest('location')
                    );


                    $users = Model_Users::getUsers($data);
                    if ($request->issetPost('location'))
                    {
                        if ($request->getRequest('location') != "")
                        {
                            $users_id = Model_Users::getLocationUsers($request->getRequest('location'), 1);
                            foreach ($users_id as $user_id)
                            {
                                $dataUser = array(
                                    'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                    'limit' => JO_Registry::get('config_front_limit'),
                                    'filter_user_id' => $user_id["user_id"]
                                );
                                $userAux = Model_Users::getUsers($dataUser);
                                //$users[] = Model_Users::getUsers($dataUser);
                                if ($userAux)
                                {
                                    $userAux[0]["location"] = $user_id["location"];
                                    $userAux[0]["lat"] = $user_id["lat"];
                                    $userAux[0]["len"] = $user_id["len"];
                                    //array_push($users, $userAux);
                                    $users[] = $userAux[0];

                                }
                            }
                        }
                    }
                    if ($request->issetPost('sport_category_1'))
                    {
                        $users_id = Model_Users::getUserIdSportsUser($request->getRequest('sport_category_1'));
                        foreach ($users_id as $user_id)
                        {
                            $dataUser = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_user_id' => $user_id["user_id"]
                            );
                            $userAux = Model_Users::getUsers($dataUser);
                            if ($userAux)
                            {
                                $users[] = $userAux[0];
                            }
                        }
                    }
                    if ($users)
                    {
                        $href = "";
                        $i = 0;
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
                            $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
                            $user['follow'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id']);

                            $view->key = $key % 2 == 0;
                            $view->user = $user;
                            $this->view->pins .= $view->render('boxSearch', 'users');
                            $users[$i]["href"] = $href;
                            $i++;
                        }
                        $this->view->users = $users;                        
                    }
                }
                else if ($id == "voluntarios")
                {
                    $dataVoluntarios = array(
                        'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                        'limit' => JO_Registry::get('config_front_limit'),
                        'filter_location' => $request->getRequest('location'),
                        'filter_sport_category' => $request->getRequest('sport_category'),
                        'filter_option1' => $request->getRequest('option1'),
                        'filter_option2' => $request->getRequest('option2'),
                        'filter_option3' => $request->getRequest('option3'),
                        'filter_option4' => $request->getRequest('option4'),
                        'filter_option5' => $request->getRequest('option5'),
                        'filter_option6' => $request->getRequest('option6'),
                        'filter_option7' => $request->getRequest('option7'),
                        'filter_option8' => $request->getRequest('option8'),
                        'filter_option9' => $request->getRequest('option9'),
                        'filter_option10' => $request->getRequest('option10'),                        
                        'filter_option11' => $request->getRequest('option11'),
                        'filter_option12' => $request->getRequest('option12'),
                        'filter_option13' => $request->getRequest('option13'),
                        'filter_option14' => $request->getRequest('option14'),
                        'filter_option15' => $request->getRequest('option15'),
                        'filter_option16' => $request->getRequest('option16'),
                        'filter_option17' => $request->getRequest('option17'),
                        'filter_option18' => $request->getRequest('option18')
                        
                    );

                    $voluntario = Model_Users::getUsersVoluntarios($dataVoluntarios);

                    if ($voluntario)
                    {
                        $usersTot = array();
                        foreach ($voluntario as $voluntario_user_id)
                        {

                            $data = array(
                                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                                'limit' => JO_Registry::get('config_front_limit'),
                                'filter_user_id' => $voluntario_user_id["user_id"]
                            );

                            $users = Model_Users::getUsers($data);
                            if ($users)
                            {
                                $href = "";
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
                                    $href = WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $user['user_id']);
                                    $user['follow'] = WM_Router::create($request->getBaseUrl() . '?controller=users&action=follow&user_id=' . $user['user_id']);

                                    $view->key = $key % 2 == 0;
                                    $user["location"] = $voluntario_user_id["location"];
                                    $user["sport_category"] = Model_Boards::getCategoryTitle($voluntario_user_id["sport_category"]);
                                    $user["option1"] = $voluntario_user_id["option1"];
                                    $user["option2"] = $voluntario_user_id["option2"];
                                    $user["option3"] = $voluntario_user_id["option3"];
                                    $user["option4"] = $voluntario_user_id["option4"];
                                    $user["option5"] = $voluntario_user_id["option5"];
                                    $user["option6"] = $voluntario_user_id["option6"];
                                    $user["option7"] = $voluntario_user_id["option7"];
                                    $user["option8"] = $voluntario_user_id["option8"];
                                    $user["option9"] = $voluntario_user_id["option9"];                                    
                                    $user["option10"] = $voluntario_user_id["option10"];
                                    $user["option11"] = $voluntario_user_id["option11"];
                                    $user["option12"] = $voluntario_user_id["option12"];
                                    $user["option13"] = $voluntario_user_id["option13"];
                                    $user["option14"] = $voluntario_user_id["option14"];
                                    $user["option15"] = $voluntario_user_id["option15"];
                                    $user["option16"] = $voluntario_user_id["option16"];
                                    $user["option17"] = $voluntario_user_id["option17"];
                                    $user["option18"] = $voluntario_user_id["option18"];
                                    $user["comment"] = $voluntario_user_id["comment"];
                                    $view->user = $user;
                                    $this->view->addMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=create');                                                                                        
                                    $this->view->stateMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=state');
                                    $this->view->viewMail = WM_Router::create($request->getBaseUrl() . '?controller=mails&action=view');
                                    $this->view->pins .= $view->render('boxVoluntarios', 'users');
                                }
                                $users[0]["location"] = $voluntario_user_id["location"];
                                $users[0]["lat"] = $voluntario_user_id["lat"];
                                $users[0]["len"] = $voluntario_user_id["len"];
                                $users[0]["href"] = $href;
                                $usersTot[] = $users[0];
                            }
                        }
                        $this->view->users = $usersTot;
                        $this->view->class_contaner = 'persons';
                    }
                }
            }
            if ($this->view->isPost == "true" && !$this->view->pins && !$this->view->error)
            {
                $this->view->error = $this->translate("La búsqueda no ha devuelto resultados");
            }
            
        }

        if ($request->isXmlHttpRequest())
        {
            echo $this->view->pins;
            $this->noViewRenderer(true);
        } else
        {
            $this->view->children = array(
                'header_part' => 'layout/header_part',
                'footer_part' => 'layout/footer_part'
            );
        }


    }

    public function MAXactivatedAction()
    {
        $request = $this->getRequest();

        //$this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');
        $this->view->advanced_url = WM_Router::create($request->getBaseUrl() . '?controller=search&action=index');

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $id = $request->getRequest('id');

        //////////// Categories ////////////
        $this->view->categories = array();
        $categories = Model_Categories::getCategories(array(
                    'filter_status' => 1
                ));

        foreach ($categories as $category)
        {
            $category['subcategories'] = Model_Categories::getSubcategories($category['category_id']);
            $this->view->categories[] = $category;
        }


        //////////// User Type ////////////
        $this->view->user_types = array();
        $user_types = Model_Users::getUserType(array(
                    'filter_status' => 1
                ));

        //////////// Age ////////////
        $this->view->ages = array();
        $ages = Model_Users::getAge();
        $this->view->ages = $ages;

        //////////// Level ////////////
        $this->view->levels = array();
        $levels = Model_Users::getLevel();
        $this->view->levels = $levels;


        if ($request->issetPost('firstname'))
        {
            $this->view->firstname = $request->getPost('firstname');
        } else
        {
            $this->view->firstname = '';
        }

        $this->view->location = '';
        if ($request->issetPost('location'))
        {
            $this->view->location = $request->getPost('location');
        } else
        {
            $this->view->location = '';
        }
        $this->view->cat_title1 = '';
        $this->view->sport_category_1 = '';
        if ($request->issetPost('sport_category_1'))
        {
            $this->view->sport_category_1 = $request->getPost('sport_category_1');
            if ($request->getPost('sport_category_1') != "")
            {
                if ($request->getPost('sport_category_1') == 1)
                {
                    $this->view->cat_title1 = "Todo";
                } else
                {
                    $this->view->cat_title1 = Model_Boards::getCategoryTitle($request->getPost('sport_category_1'));
                }
            }
        } else
        {
            $this->view->sport_category_1 = '';
        }
        $this->view->cat_title2 = '';
        $this->view->sport_category_2 = '';
        if ($request->issetPost('sport_category_2'))
        {
            $this->view->sport_category_2 = $request->getPost('sport_category_2');
            if ($request->getPost('sport_category_2') != "")
            {
                if ($request->getPost('sport_category_2') == 1)
                {
                    $this->view->cat_title2 = "Todo";
                } else
                {
                    $this->view->cat_title2 = Model_Boards::getCategoryTitle($request->getPost('sport_category_2'));
                }
            }
        } else
        {
            $this->view->sport_category_2 = '';
        }
        $this->view->cat_title3 = '';
        $this->view->sport_category_3 = '';
        if ($request->issetPost('sport_category_3'))
        {
            $this->view->sport_category_3 = $request->getPost('sport_category_3');
            if ($request->getPost('sport_category_3') != "")
            {
                if ($request->getPost('sport_category_3') == 1)
                {
                    $this->view->cat_title3 = "Todo";
                } else
                {
                    $this->view->cat_title3 = Model_Boards::getCategoryTitle($request->getPost('sport_category_3'));
                }
            }
        } else
        {
            $this->view->sport_category_3 = '';
        }
        $this->view->usertype_title = '';
        $this->view->type_user = '';
        if ($request->issetPost('type_user'))
        {
            $this->view->type_user = $request->getPost('type_user');
            if ($request->getPost('type_user') != "")
            {
                $this->view->usertype_title = Model_Users::getUserTypeTitle($request->getPost('type_user'));
            }
        } else
        {
            $this->view->type_user = '';
        }

        //gender
        if ($request->issetPost('gender'))
        {
            $this->view->gender = $request->getRequest('gender');
        } elseif (isset($user_data['gender']))
        {
            $this->view->gender = $user_data['gender'];
        } else
        {
            $this->view->gender = "";
        }

        //location		
        if ($request->issetPost('location'))
        {
            $this->view->location = $request->getPost('location');
        } elseif (isset($user_data['location']))
        {
            $this->view->location = $user_data['location'];
        } else
        {
            $this->view->location = '';
        }

        //sport category
        if ($request->issetPost('sport_category'))
        {
            $this->view->sport_category = $request->getPost('sport_category');
            if ($request->getPost('sport_category') != "")
            {
                $this->view->cat_title = Model_Boards::getCategoryTitle($request->getPost('sport_category'));
            }
        } elseif (isset($user_data['sport_category']))
        {
            $this->view->sport_category = $user_data['sport_category'];
            $this->view->cat_title = Model_Boards::getCategoryTitle($user_data['sport_category']);
        } else
        {
            $this->view->cat_title = '';
            $this->view->sport_category = '';
        }

        //age
        if ($request->issetPost('age'))
        {
            $this->view->age = $request->getPost('age');
            if ($request->getPost('age') != "")
            {
                $this->view->age_title = Model_Users::getAgeTitle($request->getPost('age'));
            }
        } elseif (isset($user_data['age']))
        {
            $this->view->age = $user_data['age'];
            $this->view->age_title = Model_Users::getAgeTitle($user_data['age']);
        } else
        {
            $this->view->age_title = '';
            $this->view->age = '';
        }

        //level
        if ($request->issetPost('level'))
        {
            $this->view->level = $request->getPost('level');
            if ($request->getPost('level') != "")
            {
                $this->view->level_title = Model_Users::getLevelTitle($request->getPost('level'));
            }
        } elseif (isset($user_data['level']))
        {
            $this->view->level = $user_data['level'];
            $this->view->level_title = Model_Users::getLevelTitle($user_data['level']);
        } else
        {
            $this->view->level_title = '';
            $this->view->level = '';
        }

        //option1		
        if ($request->issetPost('option1'))
        {
            $this->view->option1 = $request->getPost('option1');
        } elseif (isset($user_data['option1']))
        {
            $this->view->option1 = $user_data['option1'];
        } else
        {
            $this->view->option1 = '';
        }

        //option2		
        if ($request->issetPost('option2'))
        {
            $this->view->option2 = $request->getPost('option2');
        } elseif (isset($user_data['option2']))
        {
            $this->view->option2 = $user_data['option2'];
        } else
        {
            $this->view->option2 = '';
        }

        //option3
        if ($request->issetPost('option3'))
        {
            $this->view->option3 = $request->getPost('option3');
        } elseif (isset($user_data['option3']))
        {
            $this->view->option3 = $user_data['option3'];
        } else
        {
            $this->view->option3 = '';
        }

        //option4		
        if ($request->issetPost('option4'))
        {
            $this->view->option4 = $request->getPost('option4');
        } elseif (isset($user_data['option4']))
        {
            $this->view->option4 = $user_data['option4'];
        } else
        {
            $this->view->option4 = '';
        }

        //option5		
        if ($request->issetPost('option5'))
        {
            $this->view->option5 = $request->getPost('option5');
        } elseif (isset($user_data['option5']))
        {
            $this->view->option5 = $user_data['option5'];
        } else
        {
            $this->view->option5 = '';
        }

        //option6		
        if ($request->issetPost('option6'))
        {
            $this->view->option6 = $request->getPost('option6');
        } elseif (isset($user_data['option6']))
        {
            $this->view->option6 = $user_data['option6'];
        } else
        {
            $this->view->option6 = '';
        }

        //option7		
        if ($request->issetPost('option7'))
        {
            $this->view->option7 = $request->getPost('option7');
        } elseif (isset($user_data['option7']))
        {
            $this->view->option7 = $user_data['option7'];
        } else
        {
            $this->view->option7 = '';
        }

        //option8		
        if ($request->issetPost('option8'))
        {
            $this->view->option8 = $request->getPost('option8');
        } elseif (isset($user_data['option8']))
        {
            $this->view->option8 = $user_data['option8'];
        } else
        {
            $this->view->option8 = '';
        }

        //option9		
        if ($request->issetPost('option9'))
        {
            $this->view->option9 = $request->getPost('option9');
        } elseif (isset($user_data['option9']))
        {
            $this->view->option9 = $user_data['option9'];
        } else
        {
            $this->view->option9 = '';
        }

        //option10		
        if ($request->issetPost('option10'))
        {
            $this->view->option10 = $request->getPost('option10');
        } elseif (isset($user_data['option10']))
        {
            $this->view->option10 = $user_data['option10'];
        } else
        {
            $this->view->option10 = '';
        }

        //controlador
        /*
          if ($id == "amatteur")
          {
          $this->view->advancedActive = "amatteur";
          $with_action = $request->getAction();
          $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');

          }
          else if ($id == "activate")
          {
         * */

        $this->view->advancedActive = "activate";
        $with_action = $request->getAction();
        $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=activate');
        /*
          }
          else if ($id == "services")
          {
          $this->view->advancedActive = "services";
          $with_action = $request->getAction();
          $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action=advanced');

          }
         */
        if ($request->isPost())
        {

            $query = $request->getRequest('words');

            $this->view->query = $query;

            //$this->view->menuSearch = $this->searchMenu($query);

            $data = array(
                'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
                'limit' => JO_Registry::get('config_front_limit'),
                'filter_description' => $query,
                'filter_marker' => $request->getRequest('marker')
            );

            $this->view->pins = '';

            $pins = Model_Pins::getPins($data);
            if ($pins)
            {
                foreach ($pins AS $pin)
                {
                    $this->view->pins .= Helper_Pin::returnHtml($pin);
                }
                // 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
            }
        }


        /*
          if(in_array($request->getAction(), array('index', 'page', 'view'))) {
          $with_action = 0;
          $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search');
          } elseif( in_array($request->getAction(), array('boards', 'people')) ) {
          $with_action = $request->getAction();
          $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search&action='.$request->getAction());
          } else {
          $with_action = 0;
          $this->view->search_action = WM_Router::create($request->getBaseUrl() . '?controller=search');
          }

          $this->view->search_autocomplete = WM_Router::create($request->getBaseUrl() . '?controller=search&action=autocomplete');
          if(strpos($this->view->search, '?') !== false) {
          $this->view->show_hidden = true;
          $this->view->with_action = $with_action;
          }

          $this->view->keywords = $request->issetQuery('q') ? $request->getQuery('q') : $this->translate('Search...');
         */

        $this->view->children = array(
            'header_part' => 'layout/header_part',
            'footer_part' => 'layout/footer_part'
        );
    }

    public function indexAction()
    {

        $request = $this->getRequest();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $query = $request->getRequest('q');

        $this->view->query = $query;

        $this->view->menuSearch = $this->searchMenu($query);

        $data = array(
            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
            'limit' => JO_Registry::get('config_front_limit'),
            'filter_description' => $query,
            'filter_marker' => $request->getRequest('marker')
        );

        $this->view->pins = '';

        $pins = Model_Pins::getPins($data);
        if ($pins)
        {
            foreach ($pins AS $pin)
            {
                $this->view->pins .= Helper_Pin::returnHtml($pin);
            }
// 			JO_Registry::set('marker', Model_Pins::getMaxPin($data));
        }

        if ($request->isXmlHttpRequest())
        {
            echo $this->view->pins;
            $this->noViewRenderer(true);
        } else
        {
            $this->view->children = array(
                'header_part' => 'layout/header_part',
                'footer_part' => 'layout/footer_part'
            );
        }
    }

    public function pageAction()
    {
        $this->forward('search', 'index');
    }

    public function viewAction()
    {
        $this->forward('search', 'index');
    }

    public function peopleAction()
    {

        $this->setViewChange('index');

        $request = $this->getRequest();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $query = $request->getRequest('q');

        $this->view->query = $query;

        $this->view->menuSearch = $this->searchMenu($query);

        $data = array(
            'start' => ( JO_Registry::get('config_front_limit') * $page ) - JO_Registry::get('config_front_limit'),
            'limit' => JO_Registry::get('config_front_limit'),
            'filter_username' => $query
        );

        $this->view->pins = '';

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

                $view->key = $key % 2 == 0;
                $view->user = $user;
                $this->view->pins .= $view->render('boxSearch', 'users');
            }
        }

        if ($request->isXmlHttpRequest())
        {
            echo $this->view->pins;
            $this->noViewRenderer(true);
        } else
        {
            $this->view->children = array(
                'header_part' => 'layout/header_part',
                'footer_part' => 'layout/footer_part'
            );
        }
    }

    public function boardsAction()
    {

        $this->setViewChange('index');

        $request = $this->getRequest();

        $page = (int) $request->getRequest('page');
        if ($page < 1)
        {
            $page = 1;
        }

        $query = $request->getRequest('q');

        $this->view->query = $query;

        $this->view->menuSearch = $this->searchMenu($query);

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


                $view->board = $board;
                $this->view->pins .= $view->render('box', 'boards');
            }
        }

        if ($request->isXmlHttpRequest())
        {
            echo $this->view->pins;
            $this->noViewRenderer(true);
        } else
        {
            $this->view->children = array(
                'header_part' => 'layout/header_part',
                'footer_part' => 'layout/footer_part'
            );
        }
    }

    public function autocompleteAction()
    {
        $request = $this->getRequest();

        $this->view->items = array();

        if (JO_Session::get('user[user_id]') && $request->getPost('value'))
        {

            $friends = Model_Users::getUserFriends(array(
                        'filter_username' => $request->getPost('value')
                    ));

            if ($friends)
            {
                $model_images = new Helper_Images();
                foreach ($friends AS $friend)
                {
                    if (!isset($friend['store']))
                    {
                        continue;
                    }
                    $avatar = Helper_Uploadimages::avatar($friend, '_A');
                    $this->view->items[] = array(
                        'image' => $avatar['image'],
                        'label' => $friend['fullname'],
                        'value' => $friend['user_id'],
                        'href' => WM_Router::create($request->getBaseUrl() . '?controller=users&action=profile&user_id=' . $friend['user_id']),
                        'username' => $friend['username']
                    );
                }
            }

            $boards = Model_Boards::getBoards(array(
                        'filter_user_id' => JO_Session::get('user[user_id]'),
                        'friendly' => JO_Session::get('user[user_id]'),
                        'filter_title' => $request->getPost('value'),
                        'sort' => 'asc',
                        'order' => 'boards.title'
                    ));

            if ($boards)
            {
                foreach ($boards AS $board)
                {
                    $this->view->items[] = array(
                        'image' => $request->getBaseUrl() . 'data/images/typeahead_board.png',
                        'label' => $board['title'],
                        'value' => $board['board_id'],
                        'href' => WM_Router::create($request->getBaseUrl() . '?controller=boards&action=view&user_id=' . $board['user_id'] . '&board_id=' . $board['board_id']),
                        'username' => $board['title']
                    );
                }
            }
        }

        $this->view->items[] = array(
            'search_for' => 1,
            'label' => sprintf($this->translate('Search for %s'), $request->getPost('value')),
            'href' => WM_Router::create($request->getBaseUrl() . '?controller=search&q=' . $request->getPost('value'))
        );

        if ($request->isXmlHttpRequest())
        {
            echo $this->renderScript('json');
        } else
        {
            $this->forward('error', 'error404');
        }
    }

}

?>
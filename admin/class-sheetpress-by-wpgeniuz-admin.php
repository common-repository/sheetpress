<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       wpgeniuz.com
 * @since      1.0.0
 *
 * @package    Sheetpress_By_Wpgeniuz
 * @subpackage Sheetpress_By_Wpgeniuz/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sheetpress_By_Wpgeniuz
 * @subpackage Sheetpress_By_Wpgeniuz/admin
 * @author     Wpgeniuz <info@wpgeniuz.com>
 */
class Sheetpress_By_Wpgeniuz_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function sheetpress_enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the sheetpress_run() function
         * defined in Sheetpress_By_Wpgeniuz_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Sheetpress_By_Wpgeniuz_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/sheetpress-by-wpgeniuz-admin.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function sheetpress_enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the sheetpress_run() function
         * defined in Sheetpress_By_Wpgeniuz_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Sheetpress_By_Wpgeniuz_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/sheetpress-by-wpgeniuz-admin.js', array(
            'jquery'
        ), $this->version, false);

    }

    public function sheetpress_action_add_menu()
    {
        add_menu_page('SheetPress', 'SheetPress', 'manage_options', 'Sheetpress_manage_your_content_page', array(
            $this,
            'Sheetpress_manage_your_content_page'
        ), 'dashicons-media-text', 6);
    }

    public function Sheetpress_manage_your_content_page()
    {


        if (!empty($_GET['code']) and !empty(get_option('sheetpress_client_id')) and !empty(get_option('sheetpress_client_secret'))) {
            $client_id = get_option('sheetpress_client_id');
            $redirect_uri = site_url() . '/wp-admin/admin.php?page=Sheetpress_manage_your_content_page';
            $client_secret = get_option('sheetpress_client_secret');
            
			
			
			$url = "https://accounts.google.com/o/oauth2/token";
			$code = $_REQUEST['code'];
			$body = array(
						'code' => $code,
						'client_id' => $client_id,
						'client_secret' => $client_secret,
						'redirect_uri' => $redirect_uri,
						'grant_type' => 'authorization_code'
					);
			$contenttype = 'array';
			$data = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);	
			$contenttype='application/json';
			
            if ($data['response']['code'] == 200 and $data['response']['message'] == 'OK') {
                $data_jsondecoded = json_decode($data['body']);
		
                if (!empty($data_jsondecoded->access_token) and !empty($data_jsondecoded->expires_in) and is_numeric($data_jsondecoded->expires_in)) {
                    $data_jsondecoded->expires_in = time() + intval($data_jsondecoded->expires_in);

                    $sheetpress_wordpress_contents_pages = get_option('sheetpress_wordpress_contents_pages');
                
				   //check sheet exist in db so not create it again.
                    if (empty($sheetpress_wordpress_contents_pages->spreadsheetId)) {
						
						
			$url = "https://sheets.googleapis.com/v4/spreadsheets?access_token=" . $data_jsondecoded->access_token;
			$body = "{\"properties\":{\"title\":\"Wordpress Pages\"}}";
			$access_token = $data_jsondecoded->access_token;	
			$response_create_page_sheet = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);													
			if ( is_wp_error( $response_create_page_sheet ) ) {
				$error_message = $response_create_page_sheet->get_error_message();
				update_option('sheetpress_wordpress_contents_pages_curl_error', $response_create_page_sheet['response']['code'] .' '. $response_create_page_sheet['response']['message']);

			} else {
				if($response_create_page_sheet['response']['code'] == 200 and $response_create_page_sheet['response']['message'] == 'OK'){
							update_option('sheetpress_wordpress_contents_pages', json_decode($response_create_page_sheet['body']));
                            $sheetpress_wordpress_contents_pages = get_option('sheetpress_wordpress_contents_pages');


							$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $sheetpress_wordpress_contents_pages->spreadsheetId . "/values/Sheet1%21A1%3AE1:append?valueInputOption=USER_ENTERED";
							$body = "{\"range\":\"Sheet1!A1:E1\",\"majorDimension\":\"ROWS\",\"values\":[[\"Page ID\",\"Page Title\",\"Page Description\"]]}";
							$access_token = $data_jsondecoded->access_token;	
							$response = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);													
							if ( is_wp_error( $response ) ) {
								$error_message = $response->get_error_message();
								update_option('sheetpress_wordpress_first_row_error',$error_message);

							} else {
								if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
									update_option('sheetpress_wordpress_first_row', json_decode($response['body']));

								} else {
									update_option('sheetpress_wordpress_first_row_error',$response['response']['code'] .' '. $response['response']['message']);
								}
							}	
                            
				} else {
					update_option('sheetpress_wordpress_contents_pages_curl_error', $response_create_page_sheet['response']['code'] .' '. $response_create_page_sheet['response']['message']);

				}
			}		

                    }


                    //sheetpress_for_posts
                    $sheetpress_wordpress_contents_post = get_option('sheetpress_wordpress_contents_post');
                    //check sheet exist in db so not create it again.
                    if (empty($sheetpress_wordpress_contents_post->spreadsheetId)) {
						$url = "https://sheets.googleapis.com/v4/spreadsheets?access_token=" . $data_jsondecoded->access_token;
						$body = "{\"properties\":{\"title\":\"Wordpress Posts Contents\"}}";
						$access_token = $data_jsondecoded->access_token;	
						$response_creating_post_sheet = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);													
						if ( is_wp_error( $response_creating_post_sheet ) ) {
							$error_message = $response_creating_post_sheet->get_error_message();
							update_option('sheetpress_wordpress_contents_posts_curl_error', $error_message);
						} else {
							if($response_creating_post_sheet['response']['code'] == 200 and $response_creating_post_sheet['response']['message'] == 'OK'){
								update_option('sheetpress_wordpress_contents_post', json_decode($response_creating_post_sheet['body']));
								$sheetpress_wordpress_contents_post = get_option('sheetpress_wordpress_contents_post');
								$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $sheetpress_wordpress_contents_post->spreadsheetId . "/values/Sheet1%21A1%3AF1:append?valueInputOption=USER_ENTERED";
								$body = "{\"range\":\"Sheet1!A1:F1\",\"majorDimension\":\"ROWS\",\"values\":[[\"Posts ID\",\"Posts Title\",\"Posts Excerpt\",\"Posts Tags\",\"Posts Categories\",\"Posts Published Date\"]]}";
								$access_token = $data_jsondecoded->access_token;	
								$response = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);													
								if ( is_wp_error( $response ) ) {
									$error_message = $response->get_error_message();
									update_option('sheetpress_wordpress_first_row_error_posts', $error_message);
								} else {
									if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
										update_option('sheetpress_wordpress_first_row_posts', json_decode($response['body']));
									} else {
										update_option('sheetpress_wordpress_first_row_error_posts', $response['response']['code'] .' '. $response['response']['message']);
									}
								}	       
							} else {
								update_option('sheetpress_wordpress_contents_posts_curl_error', $response_creating_post_sheet['response']['code'] .' '. $response_creating_post_sheet['response']['message']);
							}
						}	
						
                    }

                    update_option('sheetpress_access_token', $data_jsondecoded);
                    wp_redirect(site_url() . '/wp-admin/admin.php?page=Sheetpress_manage_your_content_page');
                } else if (is_wp_error( $response )) {
					$error_message = $response->get_error_message();
                    update_option('sheetpress_access_token_error', $error_message);
                }
            } else {
				update_option('sheetpress_access_token_error', $data['response']['code'] .' '. $data['response']['message']);
			}

        }
        // save settings
        require_once(plugin_dir_path(dirname(__FILE__)) . '/admin/partials/sheetpress-by-wpgeniuz-admin-display.php');
    }

    public function sheetpress_form_auth_google_function()
    {

        if (!isset($_POST['auth_google']) || !wp_verify_nonce($_POST['auth_google'], 'name_of_auth_google')) {
            print 'Sorry, your nonce did not verify.';
            exit;
        } else if ($_POST['action'] == "auth_google" and !empty($_POST['client_id']) and wp_verify_nonce($_POST['auth_google'], 'name_of_auth_google')) {


            //https://accounts.google.com/o/oauth2/auth?scope=https%3A%2F%2Fspreadsheets.google.com%2Ffeeds+https%3A%2F%2Fdocs.google.com%2Ffeeds&redirect_uri=http://shqerp.cuccfree.com/google-api-php-client-2.2.0/WoogleSheet.php&response_type=code&client_id=25876032077-t5gsrf893kl2im7qeip08qnnqukpkkge.apps.googleusercontent.com&access_type=offline

            if (is_array($_POST)) {
                update_option('sheetpress_client_id', trim($_POST['client_id']));
                update_option('sheetpress_client_secret', trim($_POST['client_secret']));
                wp_redirect('https://accounts.google.com/o/oauth2/auth?scope=https%3A%2F%2Fspreadsheets.google.com%2Ffeeds+https%3A%2F%2Fdocs.google.com%2Ffeeds&redirect_uri=' . site_url() . '/wp-admin/admin.php?page=Sheetpress_manage_your_content_page&response_type=code&client_id=' . trim($_POST['client_id']) . '&access_type=offline&approval_prompt=force');
            } else {
                // update_option('upsell_product_notice',json_encode(array('status'=>'fails')));
                // wp_safe_redirect( site_url().'/wp-admin/admin.php?page=upsell_settings_page' );
                exit;
            }
            return false;
        }
    }

    public function sheetpress_form_manual_sync_function()
    {

        if (wp_verify_nonce($_POST['auth_google'], 'name_of_auth_google') and $_POST['action'] == "manual_sync") {


            $sheetpress_access_token = get_option('sheetpress_access_token');
													
			
            if (($sheetpress_access_token->expires_in - 300) <= time()) {
                //refresh token
                $sheetpress_access_token = $this->sheetpress_Refresh_token($sheetpress_access_token);
            }
            if (isset($_POST['deauthorize_google_account'])) {
                delete_option('sheetpress_wordpress_contents_post');
                delete_option('sheetpress_wordpress_first_row');
                delete_option('sheetpress_wordpress_contents_pages');
                delete_option('sheetpress_wordpress_contents_posts_last_updated');
                delete_option('sheetpress_previous_count_posts');
                delete_option('sheetpress_wordpress_contents_posts_clear_updated');
                delete_option('sheetpress_wordpress_contents_pages_clear_updated');
                delete_option('sheetpress_wordpress_first_row_posts');
                delete_option('sheetpress_access_token');
                delete_option('sheetpress_wordpress_contents_pages_last_updated');
                delete_option('sheetpress_previous_count_pages');
                $redirect_uri = site_url() . '/wp-admin/admin.php?page=Sheetpress_manage_your_content_page';
                wp_redirect($redirect_uri);
            } else if (isset($_POST['manual_sync_pages'])) {
                $sheetpress_wordpress_contents_pages = get_option('sheetpress_wordpress_contents_pages');
                $args = array(
                    'sort_order' => 'asc',
                    'sort_column' => 'post_title',
                    'hierarchical' => 1,
                    'exclude' => '',
                    'include' => '',
                    'meta_key' => '',
                    'meta_value' => '',
                    'authors' => '',
                    'child_of' => 0,
                    'parent' => -1,
                    'exclude_tree' => '',
                    'number' => '',
                    'offset' => 0,
                    'post_type' => 'page',
                    'post_status' => 'publish'
                );
                $pages = get_pages($args);


                if (!empty($sheetpress_wordpress_contents_pages->spreadsheetId) and !empty($sheetpress_access_token->access_token) and time() <= ($sheetpress_access_token->expires_in - 300)) {


                    $previous_count = get_option('sheetpress_previous_count_pages');
                    if (!empty($previous_count)) {
						
						
						
			$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $sheetpress_wordpress_contents_pages->spreadsheetId . "/values/A1%3AE" . $previous_count . ":clear";
			$body = "{}";
			$access_token = $sheetpress_access_token->access_token;	
			$response = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);													
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				update_option('sheetpress_wordpress_contents_pages_clear_updated_error', $error_message);
			} else {
				if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
					$response = json_decode($response);
					update_option('sheetpress_wordpress_contents_pages_clear_updated', $response['body']);
				} else {
					update_option('sheetpress_wordpress_contents_pages_clear_updated_error', $response['response']['code'] .' '. $response['response']['message']);
				}
				update_option('sheetpress_wordpress_contents_pages_clear_updated_error',$error_message);

			}	
						
                    }

                    $count = 1;
                    $content = null;
                    $last_key = key(array_slice($pages, -1, 1, TRUE));
					if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
						$content .= "[\"Page ID\",\"Page Title\", \"Page Seo Title\", \"Page Seo Description\", \"Page Seo keywords\"],";
                    }else{
						$content .= "[\"Page ID\",\"Page Title\"],";
                    }
                    if (!empty($pages)) {
                        foreach ($pages as $key => $page) {
                            if ($last_key == $key) {
                                $comma = '';
                            } else {
                                $comma = ',';
                            }
                            // $post_content = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($page->post_content))))));
                            //$post_content = strip_tags($page->post_content);
						if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
							$post_seotitle = get_post_meta($page->ID, '_yoast_wpseo_title', true);
                            $post_yoast_wpseo_metadesc = get_post_meta($page->ID, '_yoast_wpseo_metadesc', true);
                            $post_yoast_wpseo_focuskw = get_post_meta($page->ID, '_yoast_wpseo_focuskw', true);
						}
                            $content .= "[\"" . ($page->ID) . "\", \"" . $page->post_title . "\", \"$post_seotitle\", \"$post_yoast_wpseo_metadesc\", \"$post_yoast_wpseo_focuskw\"]" . $comma . "";
                            $count++;
                        }
                    }


					
					
			$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $sheetpress_wordpress_contents_pages->spreadsheetId . "/values:batchUpdate";
			$body = "{\r\n  \"valueInputOption\": \"USER_ENTERED\",\r\n  
							\"data\": [\r\n   \r\n    {\r\n      \"range\": \"Sheet1!A1:E" . $count . "\",\r\n      \"majorDimension\": \"ROWS\",\r\n      
							\"values\": [\r\n        
							" . $content . "
							]
							\r\n    }\r\n  ]\r\n}";
			$access_token = $sheetpress_access_token->access_token;	
			$response = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);													
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				update_option('sheetpress_wordpress_contents_pages_last_updated_error ',$error_message);
				update_option('upsell_product_notice', json_encode(array(
					'status' => 'fails',
					'message' => $error_message
				)));
			} else {
				if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
					$response->timestamp = time();
					update_option('sheetpress_wordpress_contents_pages_last_updated', $response);
					update_option('sheetpress_previous_count_pages', $count);
					update_option('sheetpress_wordpress_contents_pages_last_updated_record', json_decode($response['body']));
					update_option('upsell_product_notice', json_encode(array(
						'status' => 'success',
						'message' => 'All Pages synchronized !'
					)));
				} else {
					update_option('upsell_product_notice', json_encode(array(
						'status' => 'Fail',
						'message' => $response['response']['code'] .' '. $response['response']['message']
					)));
				}
				
                wp_safe_redirect(site_url() . '/wp-admin/admin.php?page=Sheetpress_manage_your_content_page');
                exit;
			}		
					
                }
            } else if (isset($_POST['manual_sync_posts'])) {

                $this->sheetpress_Sync_from_post_google($sheetpress_access_token);

            } else if (isset($_POST['manual_sync_goo_to_page'])) {
                $sheetpress_wordpress_contents_pages = get_option('sheetpress_wordpress_contents_pages');
                if (!empty($sheetpress_wordpress_contents_pages->spreadsheetId) and !empty($sheetpress_access_token->access_token) and time() <= ($sheetpress_access_token->expires_in - 300)) {
					if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
						$coll = 'E';
					}else{
						$coll = 'B';
					}
					$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $sheetpress_wordpress_contents_pages->spreadsheetId . "/values/A1%3A".$coll."";
					$response = $this->wp_remote_get_for_sheetpress($url,$sheetpress_access_token->access_token);
                    
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				update_option('sheetpress_wordpress_contents_posts_clear_updated_error', $error_message);
				update_option('upsell_product_notice', json_encode(array(
				'status' => 'fails',
				'message' => $error_message
				)));
			} else if($response['response']['code'] == 200 and $response['response']['message'] == 'OK') {
					$response = json_decode($response['body']);
			
					if (!empty($response->values)) {
						$values = $response->values;
						unset($values[0]);
						foreach ($values as $value) {
							$post = get_post($value[0]);

							if (empty($post->ID) and  $post->post_type == 'page') {
								//creating new post
								// change the Sample page to the home page
								if (is_admin()) {
									$home_page_title = $value[1];
									// $home_page_content = $value[2];
									$home_page_check = get_page_by_title($home_page_title);
									$home_page = array(
										'post_type' => 'page',
										'post_title' => $home_page_title,
										// 'post_content' => $home_page_content,
										'post_status' => 'publish',
										'post_author' => 1,
										'post_slug' => 'home'
									);
									$home_page_id = wp_insert_post($home_page);
									if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
										if (!empty($value[2])) {
											update_post_meta($home_page_id, '_yoast_wpseo_title', $value[2]);
										}
										if (!empty($value[3])) {
											update_post_meta($home_page_id, '_yoast_wpseo_metadesc', $value[3]);
										}
										if (!empty($value[4])) {
											update_post_meta($home_page_id, '_yoast_wpseo_focuskw_text_input', $value[4]);
											update_post_meta($home_page_id, '_yoast_wpseo_focuskw', $value[4]);
										}
									}
								}
							} else {
								// updating existing post
								if (is_admin()) {
									$home_page_title = $value[1];
									// $home_page_content = $value[2];
									$home_page = $post;
									$home_page->post_title = $home_page_title;
									// $home_page->post_content = $home_page_content;
									$home_page_id = wp_insert_post($home_page);
									if (!empty($value[2])) {
										update_post_meta($home_page_id, '_yoast_wpseo_title', $value[2]);
									}
									if (!empty($value[3])) {
										update_post_meta($home_page_id, '_yoast_wpseo_metadesc', $value[3]);
									}
									if (!empty($value[4])) {
										update_post_meta($home_page_id, '_yoast_wpseo_focuskw_text_input', $value[4]);
										update_post_meta($home_page_id, '_yoast_wpseo_focuskw', $value[4]);
									}

								}
							}

						}

						update_option('upsell_product_notice', json_encode(array(
							'status' => 'success',
							'message' => 'All Pages synchronized !'
						)));
						wp_safe_redirect(site_url() . '/wp-admin/admin.php?page=Sheetpress_manage_your_content_page');
						exit;
					}
			}
			}
            } else if (isset($_POST['manual_sync_goo_to_post'])) {
                $sheetpress_wordpress_contents_post = get_option('sheetpress_wordpress_contents_post');

                if (!empty($sheetpress_wordpress_contents_post->spreadsheetId) and !empty($sheetpress_access_token->access_token) and time() <= ($sheetpress_access_token->expires_in - 300)) {
					if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
						$coll = "I";
					}else{
						$coll = "F";
					}
					
					
					$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $sheetpress_wordpress_contents_post->spreadsheetId . "/values/A1%3A".$coll."";
					$response = $this->wp_remote_get_for_sheetpress($url,$sheetpress_access_token->access_token);
                    if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						update_option('sheetpress_wordpress_contents_goo_to_posts_updated_error', $error_message);
					} else if($response['response']['code'] == 200 and $response['response']['message'] == 'OK') {
					
                        $response = json_decode($response['body']);
                        if (!empty($response->values)) {
                            $values = $response->values;
                            unset($values[0]);

                            foreach ($values as $value) {

                                $new_post = new \stdClass();
                                $post = get_post($value[0]);
                                if (is_admin()) {
                                    //add new post

                                    //////////////////// Edit by Mursaleen ///////////////--start--///////////
                                    if (!empty($value[4])) {

                                        $categories = explode(',', $value[4]);
                                        $terms = array();

                                        foreach($categories as $cat_name){

                                            $cat_name = ltrim($cat_name);

                                            // check if the term exist
                                            $existing_term = get_term_by( 'name', $cat_name, 'category' );

                                            if( $existing_term ){
                                                // link to existing term with slug
                                                $terms[] = (int)$existing_term->term_id;

                                            } else {
                                                // create new term
                                                $cat_id = wp_create_category( $cat_name );
                                                $terms[] = (int)$cat_id;
                                            }

                                        }

                                        $terms = array_map( 'intval', $terms );
                                        $terms = array_unique( $terms );

                                        // link all new and existing terms to post
                                        // $response = wp_set_object_terms( $post->ID , $terms , 'category' , false);
                                        $cattoadd = $terms;

                                    } else {
										$cattoadd = '';
									}
                                    //////////////////// Edit by Mursaleen /////////////////--end--///////////

                                    /////////////////////////// Category parent child support code ///////////--start---/////////
                                    //Check if category already exists
                                    /*
                                    if (!empty($value[4])) {
                                        $category = explode('Category', $value[4]);
                                        unset($category[0]);
                                        $cattoadd = null;
                                        if (!empty($category)) {
                                            foreach ($category as $cats) {
                                                $getting_parent = explode('{', $cats);
                                                $getting_parentchck = explode('{', $cats);
                                                $getting_parentandslug = explode('|', $getting_parent[0]);
                                                $getting_parent = str_replace('[', '', $getting_parentandslug[0]);
                                                $getting_slug = str_replace(']', '', $getting_parentandslug[1]);
                                                $getting_slug = str_replace(':', '', $getting_slug);
                                                $cattoadd[] = $cat_ID = get_cat_ID($getting_parent);

                                                if (empty($getting_parentchck[1])) {
                                                    //cats who have no child
                                                    if ($cat_ID == 0) {
                                                        $my_cat = array(
                                                            'cat_name' => $getting_parent,
                                                            'category_description' => '',
                                                            'category_nicename' => $getting_slug,
                                                            'category_parent' => ''
                                                        );
                                                        // Create the category
                                                        $cattoadd[] = $cat_ID = wp_insert_category($my_cat);
                                                        //not exist

                                                    }
                                                } else {
                                                    //cats who have child

                                                    //getting child than exist or not.

                                                    $getting_childwithslug = explode('|', $getting_parentchck[1]);
                                                    $getting_child = $getting_childwithslug[0];
                                                    $getting_childSlug = str_replace('}', '', str_replace(']', '', str_replace(':', '', $getting_childwithslug[1])));
                                                    $cattoadd[] = $CheckChild_exist = get_cat_ID($getting_child);

                                                    if ($cat_ID == 0) {
                                                        $my_cat = array(
                                                            'cat_name' => $getting_parent,
                                                            'category_description' => '',
                                                            'category_nicename' => $getting_slug,
                                                            'category_parent' => ''
                                                        );
                                                        // Create the category
                                                        $cattoadd[] = $cat_ID = wp_insert_category($my_cat);
                                                    }


                                                    if ($CheckChild_exist == 0) {
                                                        $addinchild = array(
                                                            'cat_name' => $getting_child,
                                                            'category_description' => '',
                                                            'category_nicename' => $getting_childSlug,
                                                            'category_parent' => $cat_ID
                                                        );
                                                        // Create the category
                                                        $cattoadd[] = $CheckChild_exist = wp_insert_category($addinchild);
                                                    }


                                                }
                                            }
                                        }

                                    }*/
                                    /////////////////////////// Category parent child support code ///////////--end---/////////


                                    // Create post object
                                    if (!empty($post->ID)) {
                                        $new_post = $post;
                                    }
                                    $new_post->post_title = $value[1];
                                    if (!empty($value[2])) {
                                        $new_post->post_excerpt = $value[2];
                                    }
                                    $new_post->post_status = 'publish';
                                    $new_post->post_author = 1;


                                    if (!empty($cattoadd)) {
                                        $new_post->post_category = array_unique($cattoadd);
                                    }
                                    if (!empty($post->ID)
                                        and is_numeric($post->ID)
                                            and $post->post_type == 'post'
                                                and $post->post_status == 'publish'
                                    ) {
                                        $new_post->ID = $post->ID;
                                        $tagsupdated = false;
                                        $postid = wp_insert_post($new_post);
										if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
											if (!empty($value[6])) {
												update_post_meta($postid, '_yoast_wpseo_title', $value[6]);
											}
											if (!empty($value[7])) {
												update_post_meta($postid, '_yoast_wpseo_metadesc', $value[7]);
											}
											if (!empty($value[8])) {
												update_post_meta($postid, '_yoast_wpseo_focuskw_text_input', $value[8]);
												update_post_meta($postid, '_yoast_wpseo_focuskw', $value[8]);
											}
										}
                                    } else if (strtolower($value[0]) == "add") {
                                        $tagsupdated = false;
                                        $postid = wp_insert_post($new_post);
                                        if (!empty($value[6])) {
                                            update_post_meta($postid, '_yoast_wpseo_title', $value[6]);
                                        }
                                        if (!empty($value[7])) {
                                            update_post_meta($postid, '_yoast_wpseo_metadesc', $value[7]);
                                        }
                                        if (!empty($value[8])) {
                                            update_post_meta($postid, '_yoast_wpseo_focuskw_text_input', $value[8]);
                                            update_post_meta($postid, '_yoast_wpseo_focuskw', $value[8]);
                                        }
                                    }
                                    if (!empty($value[3])) {
                                        $tagsarray = null;
                                        $tagsarray = (!empty(explode(',', $value[3])) ? explode(',', $value[3]) : $value[3]);
                                        wp_set_post_terms($postid, $tagsarray, 'post_tag', $tagsupdated);

                                    } else {
                                        wp_set_post_terms($postid, array(), 'post_tag', false);
                                    }
                                }


                            }
                            // die();
                            sleep(1);
                            $this->sheetpress_Sync_from_post_google($sheetpress_access_token);
                        }
                    }
                }

            }
        }
    }

    Public function sheetpress_Sync_from_post_google($sheetpress_access_token)
    {


        $sheetpress_wordpress_contents_post = get_option('sheetpress_wordpress_contents_post');
        $args = array(
            'posts_per_page' => -1,
            'offset' => 0,
            'category' => '',
            'category_name' => '',
            'orderby' => 'date',
            'order' => 'DESC',
            'include' => '',
            'exclude' => '',
            'meta_key' => '',
            'meta_value' => '',
            'post_type' => 'post',
            'post_mime_type' => '',
            'post_parent' => '',
            'author' => '',
            'author_name' => '',
            'post_status' => 'publish',
            'suppress_filters' => true
        );
        $posts = get_posts($args);


        if (!empty($sheetpress_wordpress_contents_post->spreadsheetId) and !empty($sheetpress_access_token->access_token) and time() <= ($sheetpress_access_token->expires_in - 300)) {


            $previous_count_posts = get_option('sheetpress_previous_count_posts');
            if (!empty($previous_count_posts)) {
				
			$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $sheetpress_wordpress_contents_post->spreadsheetId . "/values/A1%3AI" . $previous_count_posts . ":clear";
			$body = "{}";
			$access_token = $sheetpress_access_token->access_token;	
			$response = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);													
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				update_option('sheetpress_wordpress_contents_posts_clear_updated_error ',$error_message);
			} else {
				if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
					update_option('sheetpress_wordpress_contents_posts_clear_updated', $response['body']);
				} else {
					update_option('sheetpress_wordpress_contents_posts_clear_updated_error ',$response['response']['code'] .' '. $response['response']['message']);

				}
				
			}	

            }

            $count = 1;
            $content = null;
            $last_key = key(array_slice($posts, -1, 1, TRUE));
            // $content .= "[\"Posts ID\",\"Posts Title\", \"Posts Excerpt\", \"Posts  Seo Title\", \"Posts  Seo Description\",\"Posts Seo keywords\",\"Posts Tags\", \"Posts Categories\", \"Posts Published Date\"],";
			if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
				$content .= "[\"Posts ID\",\"Posts Title\", \"Posts Excerpt\", \"Posts Tags\", \"Posts Categories\", \"Posts Published Date\",\"Posts  Seo Title\", \"Posts  Seo Description\",\"Posts Seo keywords\"],";
			} else {
				$content .= "[\"Posts ID\",\"Posts Title\", \"Posts Excerpt\", \"Posts Tags\", \"Posts Categories\", \"Posts Published Date\"],";
			}
			if (!empty($posts)) {
                foreach ($posts as $key => $post) {

                    ////// Edit by Mursaleen ///////--start--///////
                    $categories = get_the_category($post->ID); //$post->ID

                    if(!empty($categories)){
                        $cat_string = '';
                        foreach($categories as $cat_key => $cat){
                            $cat_string .= $cat_key == 0 ? '' : ',';
                            $cat_string .= $cat->name;
                        }
                    }
                    ////// Edit by Mursaleen ///////--end--///////

                    ////////////////// Category parent child support code /////////-- start--//////////
                    /*
                    $category_detail = get_the_category($post->ID); //$post->ID
                    $category_detail_last_key = key(array_slice($category_detail, -1, 1, TRUE));
                    $catt = null;
                    foreach ($category_detail as $keys => $cd) {
                        if ($category_detail_last_key == $keys) {
                            $semi = '';
                        } else {
                            $semi = ':';
                        }
                        $cat[$keys]['name'] = $cd->cat_name;
                        if ($cd->category_parent != 0) {
                            $term = get_term($cd->category_parent);
                            $cat[$keys]['category_parent'] = $term->name;
                            $cat[$keys]['category_parent_slug'] = $term->slug;
                            $catt .= 'Category[' . $term->name . '|' . $term->slug . '{' . $cd->cat_name . '|' . $cd->slug . '}]' . $semi;
                        } else {
                            $catt .= 'Category[' . $cd->cat_name . '|' . $cd->slug . ']';
                            $catt .= $semi;
                        }
                    }
                    */
                    ////////////////// Category parent child support code /////////-- end --//////////

                    $catt = $cat_string;

                    $tagss = wp_get_post_tags($post->ID);
                    $tgss = null;

                    if (!empty($tagss)) {
                        $tagss_last_key = key(array_slice($tagss, -1, 1, TRUE));
                        foreach ($tagss as $keytg => $tg) {
                            if ($tagss_last_key == $keytg) {
                                $keytgcomma = '';
                            } else {
                                $keytgcomma = ',';
                            }
                            $tgss .= $tg->name . $keytgcomma;
                        }
                    }
                    if ($last_key == $key) {
                        $comma = '';
                    } else {
                        $comma = ',';
                    }
                    // $post_content = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($page->post_content))))));

					
					if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
						//plugin is activated
						$post_seotitle = get_post_meta($post->ID, '_yoast_wpseo_title', true);
						$post_yoast_wpseo_metadesc = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
						$post_yoast_wpseo_focuskw = get_post_meta($post->ID, '_yoast_wpseo_focuskw', true);
					} 
					
                 // $content .= "[\"" . ($post->ID) . "\", \"" . $post->post_title . "\", \"" . $post->post_excerpt . "\", \"" . $post_seotitle . "\", \"" . $post_yoast_wpseo_metadesc . "\", \"" . $post_yoast_wpseo_focuskw . "\", \"" . $tgss . "\", \"" . $catt . "\", \"" . $post->post_date . "\"]" . $comma . "";
                    $content .= "[\"" . ($post->ID) . "\", \"" . $post->post_title . "\", \"" . $post->post_excerpt . "\", \"" . $tgss . "\", \"" . $catt . "\", \"" . $post->post_date . "\", \"" . $post_seotitle . "\", \"" . $post_yoast_wpseo_metadesc . "\", \"" . $post_yoast_wpseo_focuskw . "\"]" . $comma . "";
                    $count++;

                }

            }


			$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $sheetpress_wordpress_contents_post->spreadsheetId . "/values:batchUpdate";
			$body = "{\r\n  \"valueInputOption\": \"USER_ENTERED\",\r\n
				\"data\": [\r\n   \r\n    {\r\n      \"range\": \"Sheet1!A1:I" . $count . "\",\r\n      \"majorDimension\": \"ROWS\",\r\n      
				\"values\": [\r\n        
				" . $content . "
				]
				\r\n    }\r\n  ]\r\n}";
			$access_token = $sheetpress_access_token->access_token;	
			$response = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);													
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				update_option('sheetpress_wordpress_contents_posts_last_updated_error ',$error_message);
				update_option('upsell_product_notice', json_encode(array(
					'status' => 'fails',
					'message' => $error_message
				)));
			} else {
				if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
					$response->timestamp = time();
					update_option('sheetpress_wordpress_contents_posts_last_updated', $response);
					update_option('sheetpress_previous_count_posts', $count);
					update_option('sheetpress_wordpress_contents_pages_last_updated_record', json_decode($response['body']));
					update_option('upsell_product_notice', json_encode(array(
						'status' => 'success',
						'message' => 'All Posts synchronized !'
					)));
				} else {
					update_option('upsell_product_notice', json_encode(array(
						'status' => 'Fail',
						'message' => $response['response']['code'] .' '. $response['response']['message']
					)));
				}
				
                wp_safe_redirect(site_url() . '/wp-admin/admin.php?page=Sheetpress_manage_your_content_page');
                exit;
			}		
				
        }

    }
	
	public function wp_remote_get_for_sheetpress($url,$access_token){
		
		$args = array(
			'timeout'     => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     =>  array(
								"authorization" => "Bearer " . $access_token,
								"accept" => "application/json",
								"cache-control" => "no-cache",
								"content-type" => "application/json"
							),
			'cookies'     => array(),
			'body'        => null,
			'compress'    => false,
			'decompress'  => true,
			// 'sslverify'   => true,
			'stream'      => false,
			'filename'    => null
		); 
		$response = wp_remote_get( $url, $args );
		return $response;
	}
	public function wp_remote_post_for_sheetpress($url,$body,$access_token=null,$contenttype=null){
		if($contenttype == 'form'){
			$contype='application/x-www-form-urlencoded'; 
		} else {
			$contype='application/json';
		}
		if($contenttype != 'array'){
			$headers = array(
				"authorization" => "Bearer " . $access_token,
				"accept" => "application/json",
				"cache-control" => "no-cache",
				"content-type" => $contype
			);
		} else {
			$headers = array();
		}
		$response = wp_remote_post( $url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking' => true,
			'headers' => $headers,
			'body' => $body,
			'cookies' => array()
			)
		);
		return $response;
	}
    public function sheetpress_Refresh_token($sheetpress_access_token)
    {

        if (!empty($sheetpress_access_token->access_token)) {
			$auth_cred = array(
				'client_id' => get_option('sheetpress_client_id'),
				'client_secret' => get_option('sheetpress_client_secret'),
				'refresh_token' => $sheetpress_access_token->refresh_token,
				'grant_type' => 'refresh_token',
				'approval_prompt' => 'force',
				);
			

			$url = "https://www.googleapis.com/oauth2/v4/token";
			$body = $auth_cred;
			$access_token = $sheetpress_access_token->access_token;	
			$contenttype = 'form';
			$response = $this->wp_remote_post_for_sheetpress($url,$body,$access_token,$contenttype);		
						
			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				update_option('sheetpress_access_token_error',$error_message);
			} else {
				if($response['response']['code'] == 200 and $response['response']['message'] == 'OK'){
					$response = json_decode($response['body']);

					$sheetpress_access_token->access_token = $response->access_token;
					$sheetpress_access_token->expires_in = time() + intval($response->expires_in);

					update_option('sheetpress_access_token', $sheetpress_access_token);
					return $sheetpress_access_token;
				} else {
					update_option('sheetpress_access_token_error',$response['response']['code'] .' '. $response['response']['message']);
				}
				
			}					
            
        }
    }

}
/* 


$post = get_post($value[0]);
									
									
									if(empty($post)){
										
										//add new post
											//Check if category already exists
											// $cat_ID = get_cat_ID( $category );

											//If it doesn't exist create new category
											// if($cat_ID == 0) {
												// $cat_name = array('cat_name' => $category);
												// wp_insert_category($cat_name);
											// }

											//Get ID of category again incase a new one has been created
											// $new_cat_ID = get_cat_ID($category);

											// Create post object
											$new_post = array(
											'post_title' => $headline,
											'post_content' => $body,
											'post_excerpt' => $excerpt,
											'post_date' => $date,
											'post_date_gmt' => $date,
											'post_status' => 'publish',
											'post_author' => 1
											// 'post_category' => array($new_cat_ID)
											);

											// Insert the post into the database
											wp_insert_post( $new_post );
									} else {
										//update post
										
									} */
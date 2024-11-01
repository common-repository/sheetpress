    <?php

    /**
     * Provide a admin area view for the plugin
     *
     * This file is used to markup the admin-facing aspects of the plugin.
     *
     * @link       wpgeniuz.com
     * @since      1.0.0
     *
     * @package    Sheetpress_By_Wpgeniuz
     * @subpackage Sheetpress_By_Wpgeniuz/admin/partials
     */
    ?>

    <!-- This file should primarily consist of HTML with a little bit of PHP. -->

    <div class="wrap">
        <?php

        $upsell_product_notice = get_option('upsell_product_notice');
        $sheetpress_wordpress_last_record = get_option('sheetpress_wordpress_contents_pages_last_updated_record');

        if(!empty($upsell_product_notice)
            and json_decode($upsell_product_notice)->status === 'success'
            and empty($sheetpress_wordpress_last_record ->error)
            ){
            $upsell_product_notice = json_decode($upsell_product_notice);
            if(!empty($upsell_product_notice->message)){
                $msg = $upsell_product_notice->message;
            } else {
                $msg =  'Setting updated';
            }
        ?>
        <div class="notice notice-success is-dismissible">
                    <p><?php _e( $msg , 'upsell-product' ); ?></p>
        </div>
        <?php
        }
        if(!empty($upsell_product_notice)){
            delete_option('upsell_product_notice');
        }
        if(!empty($sheetpress_wordpress_last_record)){
            if(!empty($sheetpress_wordpress_last_record->error)){
                $class = 'notice notice-error';
                foreach($sheetpress_wordpress_last_record->error as $key => $value){ ?>
                    <div class="<?=$class?> is-dismissible">
                                <b>
                                    <p>
                                        <?php _e( $key , 'sheetpress' ); ?> :
                                        <?php _e( $value , 'sheetpress' ); ?>
                                    </p>
                                </b>
                    </div>
            <?php	}
            } else {
                $class = 'notice notice-success';

                foreach($sheetpress_wordpress_last_record as $key => $value){ if(!is_array($value)){ ?>
                    <div class="<?=$class?> is-dismissible">
                                <b>
                                    <p>

                                        <?php
                                            if($key == "timestamp"){
                                                 _e( 'Updated On' , 'sheetpress' ); ?> : <?php
                                                _e(  strftime("%Y-%m-%d, %H:%M:%S", $value) , 'sheetpress' );
                                            } else {
                                                 _e( $key , 'sheetpress' ); ?> : <?php
                                                _e( $value , 'sheetpress' );
                                            }
                                         ?>
                                    </p>
                                </b>
                    </div>
            <?php	} }
            }


        }
		delete_option('sheetpress_wordpress_contents_pages_last_updated_record');

        $upsell_product_settings = get_option('upsell_product_settings');


        ?>
        <style>
            .press-title .sheet{
                color:#21a464;
                font-weight: 900;
            }
            .press-title .press{
                color:#0073aa;
                font-weight: 900;
            }
            .press-subtitle{
                margin-top: 0px;
                color: #b1b1b1;
                font-size: 15px;
            }
            .press-container{
                padding : 40px;
            }
            .sheet-to-press{
                background-color: #21a464 !important;
                text-shadow: none !important;
            }
            .press-to-sheet{
                background-color: #0073aa;
            }
            .form-table th {
                width: 151px !important;
                display: block;
            }
            .woo-to-sheet-img, .sheet-to-woo-img{
                    height: 80px;
            }
            .section_start {
                border-top: 1px solid #efefef;
            }
            .section_start td{
               padding-top: 35px;
            }
            input.button.deauthorize_google_account {
                margin-top: 21px;
                background: #ff6262 !important;
                border-color: #aa0000 #990000 #990000;
                -webkit-box-shadow: 0 1px 0 #9e1414;
                box-shadow: 0 1px 0 #990000;
                color: #fff;
                text-decoration: none;
                text-shadow: 0 -1px 1px #990000, 1px 0 1px #990000, 0 1px 1px #990000, -1px 0 1px #990000;
            }
        </style>
        <div class="welcome-panel press-container" >
            <h2 class="press-title"><span class="sheet" >Sheet</span><span class="press" >Press</span></h2>
            <h4 class="press-subtitle">Manage WordPress With Google Sheets</h4>

            <form action="<?php echo site_url(); ?>/wp-admin/admin-post.php" method="post">


        <?php wp_nonce_field( 'name_of_auth_google', 'auth_google' );

        $sheetpress_access_token = get_option('sheetpress_access_token');
        if(!empty($sheetpress_access_token->access_token)){
            $Authorized_status = '<td>
                                        <p style="background-color: #dfffdf; padding:5px;" ><b>	Google Account Successfully Authorized!</b>

                                   </p>
                                   <div><input type="submit" value="Click here Deauthorize your google account"  onclick="return confirm(\'Do you really like to deauthorize the connected account?\')" name="deauthorize_google_account"class="deauthorize_google_account button button-primary"></div>
                                    </td>';
            $Authorized_btn = '
              <tr valign="top" class="section_start">
                        <th scope="row">
                        </th>
                        <td>
                       <p>
                                <img class="woo-to-sheet-img" src="'.site_url().'/wp-content/plugins/sheetpress/admin/images/wootoshee.jpeg">
                            </p>
                        </td>
                </tr>

            <tr valign="top">
                        <input type="hidden" name="action" value="manual_sync">
                        <th scope="row">
                            <label for="hm_sbp_field_report_time">Push <b>Pages</b></label>

                        </th>
                        <td>
                            <p class="submit">
                                <input type="submit" name="manual_sync_pages" value="Click here to synchronize Your Pages to Google Sheet" class="button button-primary press-to-sheet">
                            </p>
                            <p class="description" id="tagline-description">This will get all your Pages standard data fields value <b>except content</b> into Google Sheet</p>
                        </td>
                </tr>
                <tr valign="top">
                        <th scope="row">
                            <label for="hm_sbp_field_report_time">Push <b>Posts</b></label>
                        </th>
                        <td>
                            <p class="submit">
                                <input type="submit" name="manual_sync_posts" value="Click here to synchronize Your Posts to Google Sheet" class="button button-primary press-to-sheet">
                            </p>
                            <p class="description" id="tagline-description">This will get all your Posts standard data fields value <b>except content</b> into Google Sheet</p>
                        </td>
                </tr>
                <tr valign="top" class="section_start">
                        <th scope="row">
                        </th>
                        <td>
                        <p>
                                <img class="sheet-to-woo-img" src="'.site_url().'/wp-content/plugins/sheetpress/admin/images/shetowoo.jpeg">
                            </p>
                        </td>
                </tr>
                <tr valign="top">
                        <th scope="row">
                            <label for="hm_sbp_field_report_time">Update <b>Pages</b></label>
                        </th>
                        <td>
                            <p class="submit">
                                <input type="submit" name="manual_sync_goo_to_page" value="Click here to update Pages from Google Sheet" class="button button-primary sheet-to-press" >
                            </p>
                            <p class="description" id="tagline-description">This will update all your Pages with the data you have in Goolge Sheet</p>
                        </td>
                </tr>
                <tr valign="top">
                        <th scope="row">
                            <label for="hm_sbp_field_report_time">Update <b>Posts</b></label>
                        </th>
                        <td>
                            <p class="submit">
                                <input type="submit" name="manual_sync_goo_to_post" value="Click here to Update Posts from Google Sheet" class="button button-primary sheet-to-press">
                            </p>
                            <p class="description" id="tagline-description">This will update all your Posts with the data you have in Goolge Sheet</p>
                        </td>
                </tr>';
        } else {
            $Authorized_status = '
                                    <input type="hidden" name="action" value="auth_google">
                                    <td>
                                        <p class="submit">
                                            <input type="submit" value="Authorize your google account" class="button button-primary">
                                        </p>
                                        <p class="description" id="tagline-description">Click here to authorize your google account to create google sheet and manage your content.</p>
                                    </td>';
        }


    echo('<div id="poststuff">
            <div id="post-body" class="columns-2">
                <div id="post-body-content" style="position: relative;">
                    <form action="#hm_sbp_table" method="post">
                        <input type="hidden" name="hm_sbp_do_export" value="1" />
        ');

    echo('
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="hm_sbp_field_report_time">Client id</label>
                        </th>
                        <td>

                                <input type="text" name="client_id" value="'.get_option('sheetpress_client_id').'" style="width:550px;" >

                            <p class="description" id="tagline-description">Client id </p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="hm_sbp_field_report_time">Client Secret</label>
                        </th>
                        <td>

                                <input type="text" name="client_secret" value="'.get_option('sheetpress_client_secret').'" style="width:550px;" >

                            <p class="description" id="tagline-description">Client Secret</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="hm_sbp_field_report_time">Redirect Url</label>
                        </th>
                        <td>
                            <p class="description" id="tagline-description">Copy your redirect url and submit on google console redirect url <br><pre><b><i>'.site_url().'/wp-admin/admin.php?page=Sheetpress_manage_your_content_page</i></b></pre></p>
                        </td>
                    </tr>

                <tr valign="top">
                        <th scope="row">
                            <label for="hm_sbp_field_report_time">Authorized your google account</label>
                        </th>
                        '.$Authorized_status.'
                </tr>
                '.@$Authorized_btn.'

                </table>');
                /*
                <p class="submit">
                    <input type="submit" value="Setup" class="button button-primary">
                </p>
                */
                 ?>

            </form>
        </div>




        </div>

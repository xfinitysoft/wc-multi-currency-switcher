<?php
// Exit if directly access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/*
* Template of Wordpress Hide Post
*
*/
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'report';
?>
<div class="warp">
    <div id="icon-options-general" class="icon32"></div>
    <h1>
        <?php esc_html_e('Multi Currency' , 'xs-mcs') ?>
        <a class="xs-mcs-pro-link" href="https://codecanyon.net/item/advance-woocommerce-multi-currency-switcher/29504917" target="_blank">
            <div class="xs-mcs-button-main">
                <?php submit_button(esc_html__("Pro Version" , 'xs-mcs' ), 'secondary' , "xs-mcs-button"); ?>
            </div>
        </a>
    </h1>
    <nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">
        <a class="nav-tab <?php  if($tab =='report' ){ echo 'nav-tab-active'; } ?>" href="?page=xs-mcs-support&tab=report" class="nav-tab">
                <?php esc_html_e( 'Report a bug' , 'xs-mcs' ); ?>
        </a>
        <a class="nav-tab <?php  if($tab =='request' ){ echo 'nav-tab-active'; } ?>" href="?page=xs-mcs-support&tab=request" class="nav-tab">
                <?php esc_html_e( 'Request a Feature' , 'xs-mcs' ); ?>
        </a>
        <a class="nav-tab <?php  if($tab =='hire' ){ echo 'nav-tab-active'; } ?>" href="?page=xs-mcs-support&tab=hire" class="nav-tab">
                <?php esc_html_e( 'Hire US' , 'xs-mcs' ); ?>
        </a>
        <a class="nav-tab <?php  if($tab =='review' ){ echo 'nav-tab-active'; } ?>" href="?page=xs-mcs-support&tab=review" class="nav-tab">
                <?php esc_html_e( 'Review' , 'xs-mcs' ); ?>
        </a>

    </nav>
    <div class="tab-content">
        <?php switch ($tab) {
            case 'report':
                ?>
                <div class="xs-send-email-notice xs-top-margin">
                    <p></p>
                    <button type="button" class="notice-dismiss xs-notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.','xs-mcs');?></span></button>
                </div>
                <form method="post" class="xs_mcs_support_form">
                    <input type="hidden" name="type" value="report">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th>
                                    <label for='xs_mcs_name'><?php esc_html_e('Your Name:', 'xs-mcs'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="xs_mcs_name" name="xs_mcs_name" required="required">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th>
                                    <label for="xs_mcs_email"><?php esc_html_e('Your Email:','xs-mcs'); ?></label>
                                </th>
                                <td>
                                    <input type="email" id="xs_mcs_email" name="xs_mcs_email" required="required">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th>
                                    <label for="xs_mcs_message"><?php esc_html_e('Message:','xs-mcs'); ?></label>
                                </th>
                                <td>
                                    <textarea id="xs_mcs_message" name="xs_mcs_message" rows="12", cols="47" required="required"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="input-group">
                        <?php submit_button(__( 'Send', 'xs-mcs' ), 'primary xs-mcs-send-mail'); ?>
                        <span class="spinner xs-mail-spinner"></span> 
                    </div>
                    
                </form>
                
                <?php
                break;
            case 'request':
                ?>
                <div class="xs-send-email-notice xs-top-margin">
                    <p></p>
                    <button type="button" class="notice-dismiss xs-notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.','xs-mcs');?></span></button>
                </div>
                <form method="post" class="xs_mcs_support_form">
                    <input type="hidden" name="type" value="request">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th>
                                    <label for='xs_mcs_name'><?php esc_html_e('Your Name:', 'xs-mcs'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="xs_mcs_name" name="xs_mcs_name" required>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th>
                                    <label for="xs_mcs_email"><?php esc_html_e('Your Email:','xs-mcs'); ?></label>
                                </th>
                                <td>
                                    <input type="email" id="xs_mcs_email" name="xs_mcs_email" required>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th>
                                    <label for="xs_mcs_message"><?php esc_html_e('Message:','xs-mcs'); ?></label>
                                </th>
                                <td>
                                    <textarea id="xs_mcs_message" name="xs_mcs_message" rows="12", cols="47" required></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="input-group">
                        <?php submit_button(__( 'Send', 'xs-mcs' ), 'primary xs-mcs-send-mail'); ?>
                        <span class="spinner xs-mail-spinner"></span> 
                    </div>
                    
                </form>
                <?php
                break;
            case 'hire':
                ?>
                <h2 class="xs-top-margin"><?php esc_html_e("Hire us to customize/develope Plugin/Theme or WordPress projects" , 'xs-mcs') ?></h2>
                <div class="xs-send-email-notice">
                    <p></p>
                    <button type="button" class="notice-dismiss xs-notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.','xs-mcs');?></span></button>
                </div>
                <form method="post" class="xs_mcs_support_form">
                    <input type="hidden" name="type" value="hire">
                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th>
                                    <label for='xs_mcs_name'><?php esc_html_e('Your Name:', 'xs-mcs'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="xs_mcs_name" name="xs_mcs_name" required="required">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th>
                                    <label for="xs_mcs_email"><?php esc_html_e('Your Email:','xs-mcs'); ?></label>
                                </th>
                                <td>
                                    <input type="email" id="xs_mcs_email" name="xs_mcs_email" required="required">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th>
                                    <label for="xs_mcs_message"><?php esc_html_e('Message:','xs-mcs'); ?></label>
                                </th>
                                <td>
                                    <textarea id="xs_mcs_message" name="xs_mcs_message" rows="12", cols="47" required="required"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="input-group">
                        <?php submit_button(__( 'Send', 'xs-mcs' ), 'primary xs-mcs-send-mail'); ?>
                        <span class="spinner xs-mail-spinner"></span> 
                    </div>
                    
                </form>
                <?php
                break;
            case 'review':
            ?>
                <p class="about-description xs-top-margin"><?php esc_html_e("If you like our plugin and support than kindly share your  " , 'xs-mcs') ?> <a href="https://wordpress.org/plugins/wp-post-hide/#reviews" target="_blank"> <?php esc_html_e("feedback" , 'xs-mcs') ?> </a><?php esc_html_e("Your feedback is valuable." , 'xs-mcs') ?> </p>
            <?php
                break;
            default:
                break;
        }
            ?>
    </div>
</div>
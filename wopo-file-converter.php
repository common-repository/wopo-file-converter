<?php
/**
 * Plugin Name:       WoPo File Converter
 * Plugin URI:        https://wopoweb.com/product/wopo-file-converter-pro/
 * Description:       Convert all audio, video, document, ebook, archive, image, spreadsheet, and presentation formats
 * Version:           1.2.0
 * Requires at least: 5.2
 * Requires PHP:      8.1
 * Author:            WoPo Web
 * Author URI:        https://wopoweb.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wopo-file-converter
 * Domain Path:       /languages
 */
require_once __DIR__ . '/vendor/autoload.php';
use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;
use \CloudConvert\Models\ImportUploadTask;

function wopofc_settings_init() {
    // Register a new setting for "wporg" page.
    register_setting( 'wopofc', 'wopofc_options' );
 
    // Register a new section in the "wporg" page.
    add_settings_section(
        'wopofc_section_developers',
        __( 'Buy WoPo File Converter Pro', 'wopo-file-converter' ), 'wopofc_section_developers_callback',
        'wopofc'
    );
 
    // Register a new field in the "wporg_section_developers" section, inside the "wporg" page.
    add_settings_field(
        'api_key', 
        __( 'CloudConvert API key', 'wopo-file-converter' ),
        'wopofc_api_key_cb',
        'wopofc',
        'wopofc_section_developers',
        array(
            'label_for'         => 'api_key',
            'type'              => 'textarea',
            'description'       => __( 'Get your CloudConvert API key here: <a target="_blank" href="https://cloudconvert.com/dashboard/api/v2/keys">https://cloudconvert.com/dashboard/api/v2/keys</a>', 'wopo-file-converter'),
        )
    );

    add_settings_field(
        'wopofc_options', 
        __( 'Form Style', 'wopo-file-converter' ),
        'wopofc_api_key_cb',
        'wopofc',
        'wopofc_section_developers',
        array(
            'label_for'         => 'form_style',
            'type'              => 'select',
            'options'           => array(
                'no_style' => 'No Style',
                'style1' => 'Style 1 (Pro version)',
                'style2' => 'Style 2 (Pro version)',
            ),
        )
    );

    add_settings_field(
        'wopofc_input_ext', 
        __( 'Input file extensions (Pro version)', 'wopo-file-converter' ),
        'wopofc_api_key_cb',
        'wopofc',
        'wopofc_section_developers',
        array(
            'label_for'         => 'input_ext',
            'type'              => 'input',
            'description'       => __( 'You can set up a list of extensions that can be converted from. For example: .pdf, .jpg, .png. Leave it blank for all file types.', 'wopo-file-converter' )
        )
    );
}

function wopofc_section_developers_callback(){
    ?>
    <p id="<?php echo esc_attr( $args['id'] ); ?>">
        <?php _e( 'To activate the Pro functions, you can purchase the Pro Plugin here: <a target="_blank" href="https://wopoweb.com/product/wopo-file-converter-pro/">https://wopoweb.com/product/wopo-file-converter-pro/</a>', 'wopo-file-converter' ); ?>
    </p>
    <?php
}

function wopofc_api_key_cb( $args ) {
    // Get the value of the setting we've registered with register_setting()
    $options = get_option( 'wopofc_options' );
    $field_value = (isset($options[$args['label_for']])) ? $options[$args['label_for']] : '';
    switch ($args['type']){
        case 'textarea': ?>
            <textarea id="<?php echo esc_attr( $args['label_for'] ); ?>" name="wopofc_options[<?php echo esc_attr( $args['label_for'] ); ?>]" cols="100" rows="10"><?php echo esc_textarea($field_value) ?></textarea>
            <?php
            break;
        case 'input': ?>
            <input type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" name="wopofc_options[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr($field_value) ?>" />
            <?php
            break;
        case 'select': ?>
            <select id="<?php echo esc_attr( $args['label_for'] ); ?>" name="wopofc_options[<?php echo esc_attr( $args['label_for'] ); ?>]">
                <?php foreach($args['options'] as $value => $text){
                    $html_attr = '';
                    if ($value == $field_value){
                        $html_attr = 'selected';
                    }
                    echo '<option '. $html_attr .' value="'. esc_attr($value). '">' . esc_html($text) . '</option>';
                } ?>
            </select>
            <?php
    }
    if (isset($args['description'])){
        ?>
        <p class="description">
            <?php echo $args['description'] ?>
        </p>
        <?php
    }
    
}
 
/**
 * Register our wporg_settings_init to the admin_init action hook.
 */
add_action( 'admin_init', 'wopofc_settings_init' );

/**
 * Register a custom menu page.
 */
function wopofc_register_menu_page() {
    if ( empty ( $GLOBALS['admin_page_hooks']['wopo'] ) ){
        add_menu_page(
            __( 'WoPo', 'wopo-file-converter' ),
            'WoPo',
            'manage_options',
            'wopo',
            'wopofc_dashboard'
        );
    }
    add_submenu_page( 'wopo', 'File Converter', 'File Converter',
    'manage_options', 'wopo/file-converter','wopofc_setting_page');
}
add_action( 'admin_menu', 'wopofc_register_menu_page' );

function wopofc_dashboard(){
    ?>
    <h1>WoPo Web</h1>
    <p><strong>More plugins from me: </strong><a target="_blank" href="https://wordpress.org/plugins/tags/wopo/">https://wordpress.org/plugins/tags/wopo/</a></p>
    <p><strong>Website: </strong><a target="_blank" href="https://wopoweb.com">WoPoWeb.com</a></p>
    <p><strong>Contact me or request customize function via Email:</strong> <a target="_blank" href="mailto:wopoweb@gmail.com">wopoweb@gmail.com</a></p>
    <?php    
}

function wopofc_setting_page(){
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
 
    // add error/update messages
 
    // check if the user have submitted the settings
    // WordPress will add the "settings-updated" $_GET parameter to the url
    if ( isset( $_GET['settings-updated'] ) ) {
        // add settings saved message with the class of "updated"
        add_settings_error( 'wopofc_messages', 'wopofc_message', __( 'Settings Saved', 'wopo-file-converter' ), 'updated' );
    }
 
    // show error/update messages
    settings_errors( 'wopofc_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "wporg"
            settings_fields( 'wopofc' );
            // output setting sections and their fields
            // (sections are registered for "wporg", each field is registered to a specific section)
            do_settings_sections( 'wopofc' );
            // output save settings button
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}
 
add_shortcode('wopo-file-converter', 'wopofc_shortcode');
function wopofc_shortcode( $atts = [], $content = null) {
    wp_enqueue_script('jquery');
    wp_enqueue_script('wopo-file-converter',plugin_dir_url(__FILE__).'/assets/js/main.js',array('jquery'));
    do_action('wopofc_enqueue_style');

    ob_start();    
    // get list formarts
    $convertFormats = get_option('wopofc_convert_formats');
    if ($convertFormats === false){
        // download data if not exists
        $convertFormats = wp_remote_retrieve_body(wp_remote_get('https://api.cloudconvert.com/v2/convert/formats'));        
        update_option('wopofc_convert_formats',$convertFormats);
    }
    $convertFormats = json_decode($convertFormats);

    if (isset($_FILES['file'])){
      $ext = pathinfo(sanitize_file_name($_FILES['file']['name']), PATHINFO_EXTENSION);
      $outputExt = sanitize_text_field($_POST['convert_to']);
      $call_api = apply_filters('wopofc_do_call_api', true, $ext, $outputExt);
      $options = get_option('wopofc_options');
      //check API key empty
      if (empty($options['api_key'])){
        echo '<p style="color:red">Error: API key is empty.</p>';
      }else if ($call_api){
        $cloudconvert = new CloudConvert([
          'api_key' => trim($options['api_key']),
          'sandbox' => false
        ]);  

        $job = (new Job())
          ->addTask(new Task('import/upload','upload-my-file'))
          ->addTask(
            (new Task('convert', 'convert-my-file'))
              ->set('input', 'upload-my-file')
              ->set('output_format', $outputExt)
          )
          ->addTask(
              (new Task('export/url', 'export-my-file'))
                  ->set('input', 'convert-my-file')
        );
    
        $cloudconvert->jobs()->create($job);

        $uploadTask = $job->getTasks()->whereName('upload-my-file')[0];
    
        $cloudconvert->tasks()->upload($uploadTask, fopen($_FILES['file']['tmp_name'], 'r'), sanitize_file_name($_FILES['file']['name']));
        
        $cloudconvert->jobs()->wait($job); // Wait for job completion
        
        if ($job->getStatus()=="finished"){
          foreach ($job->getExportUrls() as $file) {
            echo '<p style="color:green">'.esc_html($_FILES['file']['name']).' converted! <a target="_blank" href="'.esc_url($file->url).'">Download '. esc_html($file->filename).' here</a> </p>';
          }
        }else{
          $tasks = $job->getTasks();
          echo '<p style="color:red">';
          foreach($tasks as $t){                
              echo esc_html($t->getMessage())."<br />";
          }
          echo '</p>';
        }
      }

      do_action('wopofc_process_upload_file', $ext, $outputExt);
    }
    ?>
    <form action="<?php the_permalink() ?>" method="POST" enctype="multipart/form-data" class="wopo-file-converter">        
        <input type="file" name="file" class="file" <?php do_action('wopofc_form_input_file_attr') ?> >
        <div class="convert-to-row">
            <span><?php _e('Convert to','wopo-file-converter') ?></span>
            <select name="convert_to" id="convert_to" disabled class="convert-to">
                <option value="">...</option>
            </select>
        </div>
        
        <input id="btn_convert" class="btn-convert" type="submit" disabled value="<?php _e('Convert','wopo-file-converter') ?>">
    </form>
    <script>
        const wopofc_convert_formats = <?php echo wp_json_encode($convertFormats) ?>;
    </script>
    <?php
    $content = ob_get_clean();
    return $content;
}
<?php
/*
Plugin Name: CCB Group List by Jeremy Johnson
Plugin URI: http://www.jerelabs.com/plugins/ccb-group-list
Description: CCB Group List by Jeremy Johnson
Version: 0.5 Alpha
Author: Jeremy Johnson
Author URI: http://www.jerelabs.com

Licensed Under: http://creativecommons.org/licenses/by-nc/3.0/
*/

/*
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
define('WP_DEBUG', true);
*/

$jerelabs_ccb_plugin_file = WP_PLUGIN_DIR . '/jerelabs_ccb_groups/jerelabs-ccb-groups-main.php';
//$plugin_path = plugin_dir_path($jerelabs_ccb_plugin_file);
$jerelabs_ccb_options = get_option('jerelabs_ccb_options');

$jerelabs_ccb_xsl_config = get_option('jerelabs_ccb_xsl_config');
$jerelabs_ccb_cache_hours = 4;

add_action('wp_enqueue_scripts', 'jerelabs_ccb_client_scripts');

add_shortcode("ccb-form", "shortcodeHandler_Form");

add_shortcode("ccbgroups","shortcodeHandler_Groups");
add_shortcode("ccbevents","shortcodeHandler_Events");

register_activation_hook(__FILE__, 'jerelabs_ccb_add_defaults_fn');

function jerelabs_ccb_add_defaults_fn() {
  global $jerelabs_ccb_options;
  global $jerelabs_ccb_xsl_config;

    $plugin_path = plugin_dir_path(__FILE__);

  if(($jerelabs_ccb_options['setting_reset']=='true')||(!is_array($jerelabs_ccb_options))) {
    $arrGeneral = array("ccb_url" => '', "api_url" => '',"api_username" => '', "api_password" => '', "setting_reset" => '', "setting_cache"=>'off',"setting_debug"=>'');
    update_option('jerelabs_ccb_options', $arrGeneral);
  }
}


// From: http://code.garyjones.co.uk/get-wordpress-plugin-version/ (COMMENT #1 by Marko)
function get_plugin_version() {
$plugin_data = get_plugin_data( __FILE__ );
$plugin_version = $plugin_data['Version'];
return $plugin_version;
}

function debugMessage($msg)
{
  global $jerelabs_ccb_options;
  if ( is_user_logged_in() )
    {
      if($jerelabs_ccb_options['setting_debug'])
      {
        echo $msg . "<BR />";
      }
    }
}

function jerelabs_errorMessage($niceMessage,$uglyMessage = "")
{
  echo "<strong>Jerelabs CCB API Plugin Error: </strong>" . $niceMessage ;

  if($uglyMessage<>"")
    echo " (" . $uglyMessage . ")<BR />";
  else
    echo "<BR />";
}

// Adapted From: http://davidwalsh.name/php-cache-function
/* gets the contents of a file if it exists, otherwise grabs and caches */
function jerelabs_get_content($file,$url,$hours = 24,$fn = '',$fn_args = '')
{
  global $jerelabs_ccb_options;

  $content = '';
  $outputFile = dirname( __FILE__ ) . '/tmp/' . $file;

  debugMessage($outputFile);

  if(array_key_exists('setting_cache',$jerelabs_ccb_options))
    if(file_exists($outputFile))
      if($jerelabs_ccb_options['setting_cache'] == 'on')
        {
          $current_time = time(); $expire_time = $hours * 60 * 60; $file_time = filemtime($outputFile);  
          if($current_time - $expire_time < $file_time)
            {
              $content = file_get_contents($outputFile);
                debugMessage('contents cached');
            }
        }

        //TEMPORARY
        //$content = file_get_contents('/Applications/MAMP/htdocs/wp-content/plugins/jerelabs-ccb/tmp/group_profile_list.xsl-group_profiles.html.xml');
        //$content = $fn($content,$fn_args);

  if($content == '')
  { 
    try
    {
      $content = jerelabs_getContentFromURL($url);
      } catch (Exception $e) {
      jerelabs_errorMessage("Error retrieving content",$e->getMessage());
    }


    try {
      if(file_put_contents($outputFile.'.xml',$content)==FALSE)
      {
        jerelabs_errorMessage("Unable to write raw xml file, is tmp directory writeable?","");
      }
    } catch (Exception $e) {
      jerelabs_errorMessage("Unable to write raw xml file, is tmp directory writeable?",$e->getMessage());
    }

    try
    {
      $old_content = $content;

      // Parse CCB Data
      if($fn) { $content = $fn($content,$fn_args); }

      if(strlen($content) == 0)
      {
        jerelabs_errorMessage("Error processing CCB response");
        jerelabs_errorMessage("Response: " . $old_content);   
        return "";
      }

    } catch (Exception $e)
    {
      jerelabs_errorMessage("Error processing CCB response",$e->getMessage());
    }


    $content.= '';

    try {
      if(file_put_contents($outputFile,$content)==FALSE)
      {
        jerelabs_errorMessage("Unable to write formatted cache file, is tmp directory writeable?","");
      }
    } catch (Exception $e) {
      jerelabs_errorMessage("Exception writing catch file.  Unable to write formatted cache file, is tmp directory writeable?",$e->getMessage());
    }
    
    debugMessage('retrieved fresh from '.$url);
  }
  

  return $content;
}

/* gets content from a URL via curl */
function jerelabs_getContentFromURL($url) {
  global $jerelabs_ccb_options;

  if($jerelabs_ccb_options['api_username'] == '')
  {
    jerelabs_errorMessage("API username is empty","");
    return "";
  }

  if($jerelabs_ccb_options['api_password'] == '')
  {
    jerelabs_errorMessage("API passwword is empty","");
    return "";
  }

  if($url == '')
  {
    jerelabs_errorMessage("URL in jerelabs_getContentFromURL is empty","");
    return "";
  }

  try {
    $ch = curl_init();
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); 
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,5);
  curl_setopt($ch, CURLOPT_USERPWD, $jerelabs_ccb_options['api_username'] . ':' . $jerelabs_ccb_options['api_password']);
  $content = curl_exec($ch);
  curl_close($ch);
  return $content;
  } catch (Exception $e) {
    jerelabs_errorMessage("Failure getting content from API",$e->getMessage());
  }
}


function fileToDOMDoc($filename) { 
    try {
      $dom= new DOMDocument; 
    $xmldata = file_get_contents($filename); 
    $xmldata = str_replace("&", "&amp;", $xmldata);  // disguise &s going IN to loadXML() 
    $dom->substituteEntities = true;  // collapse &s going OUT to transformToXML() 
    $dom->loadXML($xmldata); 
    return $dom; 
    } catch (Exception $e) {
      jerelabs_errorMessage("Error in fileToDOMDoc",$e->getMessage());
    }
} 

function stringToDOMDoc($inputString) { 
    try {
      $dom= new DOMDocument; 
    $xmldata = $inputString;
    $xmldata = str_replace("&", "&amp;", $xmldata);  // disguise &s going IN to loadXML() 
    $dom->substituteEntities = true;  // collapse &s going OUT to transformToXML() 
    $dom->loadXML($xmldata); 
    return $dom; 
    } catch (Exception $e) {
      jerelabs_errorMessage("Error in stringToDOMDoc",$e->getMessage());
    }
} 

function shortcodeHandler_form($atts)
{
  global $jerelabs_ccb_options;
  $html_output = '';
  $fixedAtts = array_change_key_case($atts,CASE_LOWER);

  if($jerelabs_ccb_options['ccb_url'] == '')
  {
    jerelabs_errorMessage("CCB URL  is empty","");
    return "";
  }

  if($fixedAtts['id'] == '')
  {
    jerelabs_errorMessage("CCB-Form ID is empty","");
    return "";
  }

  if(array_key_exists('id',$fixedAtts))
  {
    $link_href = '';
    $link_javascript = '';
    $link_baseURL = '';

    $link_baseURL = $jerelabs_ccb_options['ccb_url'] . '/w_form_response.php?form_id=' . $fixedAtts['id'];
    $link_href = 'javascript:void(0)';
    $link_javascript = "javascript:window.open('".$link_baseURL."','Form','width=735,height=750,resizable=yes,scrollbars=yes');return false;";

    if(array_key_exists('text',$fixedAtts))
    {
      $link_text = $fixedAtts['text'];
    }
    else
    {
      $link_text = $link_baseURL;
    }

    $html_output = '<a href="' . $link_href . '" onclick="' . $link_javascript . '">' . $link_text . '</a>';
  }
  else
  {
    // ID Missing
    jerelabs_errorMessage("CCB-FORM Error: Missing ID field","");
  }

  return $html_output;

}


function shortcodeHandler_Groups($atts) {
  // Process groups shortcode


    $xslFileName = 'group_profile_list_simple.xsl';
    $CCBservice = 'group_profiles';


 
  //Pass additional parameters as API parameters  
  $fixedAtts = array_change_key_case($atts,CASE_LOWER);
   //echo 'Fixed Atts: '.var_dump($fixedAtts).'</BR>';

  $ccbAtts = $fixedAtts;
  //echo 'CCB Atts: '.var_dump($ccbAtts).'</BR>';

  //Check if there is a group type filter
  if(array_key_exists('group_type',$ccbAtts))
  {
    //echo 'IN IF';
    $xslParameters['group_type'] = $ccbAtts['group_type'];
    unset($ccbAtts['group_type']);
  }
  else
  {

    //echo '<h1>Not if</h1>';
  }
  //echo 'XSL Params: '.var_dump($xslParameters).'</BR>';

    //Fix params

    $fullOutput = makeCCBCallByFile($xslFileName, $CCBservice, $ccbAtts, $xslParameters);
    //echo var_dump($xslParameters);

    $fullOutput = $fullOutput . '<script src="'. plugins_url('datatable-supplemental.js', __FILE__) .'"'.' />';
  //send back text to replace shortcode in post
  return $fullOutput;
}

function shortcodeHandler_Events($atts) {
  // Process groups shortcode


    $xslFileName = 'public_calendar_listing.xsl';
    $CCBservice = 'public_calendar_listing';


 
  //Pass additional parameters as API parameters  
  $fixedAtts = array_change_key_case($atts,CASE_LOWER);
   //echo 'Fixed Atts: '.var_dump($fixedAtts).'</BR>';

  $ccbAtts = $fixedAtts;

//Filter starting today
  $ccbAtts['date_start'] = date('Y-m-d');

//Check to see how many days
    if(array_key_exists('num_days',$ccbAtts))
      {
        if(!is_numeric($ccbAtts['num_days']))
        {
          jerelabs_errorMessage("num_days parameter is not a number","");
          return "";
        }
        
      }
      else
      {
        // No date count specified.  Default to 7
        $ccbAtts['num_days'] =7;

      }
  
  try {
          $date = date_create(date('Y-m-d'));
        //date_add($date, date_interval_create_from_date_string($ccbAtts['num_days'].' days'));
        date_modify($date, '+'.$ccbAtts['num_days'].' day');
        $ccbAtts['date_end']=date_format($date, 'Y-m-d');
        unset($ccbAtts['num_days']);
        } catch (Exception $e) {
          jerelabs_errorMessage("Unable to calculate end date",$e->getMessage());
          return "";
        }


  //echo 'XSL Params: '.var_dump($xslParameters).'</BR>';

    //Fix params

    $fullOutput = makeCCBCallByFile($xslFileName, $CCBservice, $ccbAtts, $xslParameters);
    //echo var_dump($xslParameters);

    $fullOutput = $fullOutput . '<script src="'. plugins_url('datatable-supplemental.js', __FILE__) .'"'.' />';
  //send back text to replace shortcode in post
  return $fullOutput;
}

function findXSLName($xslName)
{

  if($xslName == "")
  {
    jerelabs_errorMessage("xslName parameter to findXSLName is empty","");
    return 0;
  }

  global $jerelabs_ccb_xsl_config;
  $retVal = -1;
  for ($i=0; $i<count($jerelabs_ccb_xsl_config);$i++)
  {
    if(strtolower($jerelabs_ccb_xsl_config[$i]['name']) == strtolower($xslName))
    {
      $retVal = $i;  
    }
  }

  return $retVal;
}


function buildCCBURLByService($CCBservice, $atts)
{
  global $jerelabs_ccb_xsl_config;
  global $jerelabs_ccb_options;

  if($CCBservice == '')
  {
    jerelabs_errorMessage("CCB Service not specified","");
    return "";
  }

  if( $jerelabs_ccb_options['api_url']=="")
  {
    jerelabs_errorMessage("API URL is is empty");
    return "";
  }

  $finalURL = $jerelabs_ccb_options['api_url'] . "?srv=" . $CCBservice;

  
    if($atts != '' && count($atts)>0)
    {
      foreach($atts as $key => $value)
      {
        $finalURL = $finalURL . "&" . $key . "=" . urlencode($value);
      }
    
    }

  debugMessage($finalURL);
  return $finalURL;
}

function makeCCBCallByFile($xslFileName, $CCBservice, $atts, $xslParameters)
{
  global $jerelabs_ccb_xsl_config;
  global $jerelabs_ccb_options;
  global $jerelabs_ccb_cache_hours;

  if($xslFileName == '')
  {
    jerelabs_errorMessage("XSL File Name not specified");
    return "";
  }

  if($CCBservice == '')
  {
    jerelabs_errorMessage("CCB Service not specified");
    return "";
  }



  $cacheFileName =  $xslFileName.'-'.$CCBservice.'.html';
  
  $ccbURL = buildCCBURLByService($CCBservice,$atts);
  
  if($ccbURL == "")
    {
      jerelabs_errorMessage("Error determining CCB URL");
      return "";
    }

  $fullXSLPath = dirname( __FILE__ ) . '/' . $xslFileName;

  $xsl = file_get_contents($fullXSLPath);

  $xslParameters['file'] = $cacheFileName;
  $xslParameters['XSL'] = $xsl;


  try {
    $html_output = jerelabs_get_content($cacheFileName, $ccbURL,$jerelabs_ccb_cache_hours,'processCCBData',$xslParameters);
  } catch (Exception $e) {
    jerelabs_errorMessage("Error retrieving content",$e->getMessage());
      return "";
  }


  return $html_output;
}

function processCCBData($content, $xslParameters)
{
  $html_output = '';
  global $jerelabs_ccb_options;

  $use_errors = libxml_use_internal_errors(true);

  debugMessage("XML: ". strlen($content));
  debugMessage("XSL: ". strlen($xslParameters['XSL']));

  if(strlen($content) < 1)
  {
    jerelabs_errorMessage("Error in processCCBData, no XML retrieved.");
    return "";
  }

  if(strlen($xslParameters['XSL']) < 1)
  {
    jerelabs_errorMessage("Error in processCCBData, no XSL specified.");
    return "";
  }

  if($jerelabs_ccb_options['ccb_url'] == '')
  {
    jerelabs_errorMessage("CCB URL  is empty","");  
    return "";
  }


  try {
    $xml = stringToDOMDoc($content); 
  } catch (Exception $e) {
    jerelabs_errorMessage("Error converting XML to DOM",$e->getMessage());
    return "";
  }

  try {
    $xsl = stringToDOMDoc($xslParameters['XSL']); 
  } catch (Exception $e) {
      jerelabs_errorMessage("Error converting XSL to DOM",$e->getMessage());
      return "";  
  }

  debugMessage("XML: " . gettype($xml));
  debugMessage("XSL: " . gettype($xsl));

    // Configure the transformer 
  $proc = new XSLTProcessor; 
  $proc->importStyleSheet($xsl);
  $proc->setParameter('','ccburl',$jerelabs_ccb_options['ccb_url']);
  if(strlen($xslParameters['group_type'])>0)
    {
      $proc->setParameter('','group_type',$xslParameters['group_type']);
      debugMessage('Set Group Type:' . $xslParameters['group_type']);
    }
  $proc->setParameter('','currentDate',date('Y-m-d'));

  // transform $xml according to the stylesheet $xsl 
  try {
    $html_output =  $proc->transformToXML($xml); // transform the data 
  } catch (Exception $e) {
    jerelabs_errorMessage("Error applying XSL transformation",$e->getMessage());
  }


  libxml_clear_errors();
  libxml_use_internal_errors($use_errors);


  return $html_output;
}

// Add client javascripts
function jerelabs_ccb_client_scripts()
{
  //echo "<H1>HereIAm</H1>";

  wp_register_script( 'jqueryui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js');
  wp_enqueue_script( 'jquery' );
  

  wp_register_script('datatable','http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js','jquery','1.9.4',true);
  wp_enqueue_script('datatable');
  

  wp_register_style( 'datatablesCSS', 'http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css');
  wp_enqueue_style( 'datatablesCSS' );

}


// add the admin options page
add_action('admin_menu', 'jerelabs_ccb_admin_add_page');
function jerelabs_ccb_admin_add_page() {
add_options_page('Jerelabs CCB Settings', 'Jerelabs CCB', 'manage_options', 'jerelabs_ccb', 'jerelabs_ccb_options_page');
}

 // display the admin options page
function jerelabs_ccb_options_page() {
?>

<div>
<h2>Jerelabs CCB Integration Plugin</h2>
<span>Version <?php echo get_plugin_version(); ?>.  This is not published or maintained by CCB.  I just created this because I needed the functionality</span>

<form action="options.php" method="post">
<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
<?php settings_fields('jerelabs_options'); ?>
<?php do_settings_sections('jerelabs_ccb'); ?>

<input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
</form></div>

<?php
}

add_action('admin_init', 'jerelabs_ccb_admin_init');

function jerelabs_ccb_admin_init(){
  register_setting( 'jerelabs_options', 'jerelabs_ccb_options', 'validateSettings_General' );
  register_setting( 'jerelabs_options', 'jerelabs_ccb_xsl_config', 'validateSettings_XSL' );

  add_settings_section('section_General', 'Main Settings', 'renderSectionHeading_General', 'jerelabs_ccb');
  add_settings_field('setting_reset', 'Reset Settings On Init:', 'renderField_Reset', 'jerelabs_ccb', 'section_General');
  add_settings_field('setting_debug', 'Debug Messages for Logged on users:', 'renderField_Debug', 'jerelabs_ccb', 'section_General');
  add_settings_field('setting_cache', 'Enable Caching:', 'renderField_Cache', 'jerelabs_ccb', 'section_General');

  add_settings_section('section_CCBInfo', 'CCB Integration Settings', 'renderSectionHeadingCCB', 'jerelabs_ccb');
  add_settings_field('setting_URL', 'CCB URL:', 'renderField_URL', 'jerelabs_ccb', 'section_CCBInfo');
  add_settings_field('setting_APIURL', 'CCB API URL:', 'renderField_APIURL', 'jerelabs_ccb', 'section_CCBInfo');
  add_settings_field('setting_Username', 'CCB API Username:', 'renderField_Username', 'jerelabs_ccb', 'section_CCBInfo');
  add_settings_field('setting_Password', 'CCB API Password:', 'renderField_Password', 'jerelabs_ccb', 'section_CCBInfo');


}

function renderSectionHeading_General()
{
  echo '<p>Main description of this section here.</p>';
}


function renderSectionHeadingCCB() {
echo '<span class="section_desc">Enter the API information from <strong>Settings</strong> | <strong>API</strong> on your CCB website</span>';
}


/* CCB API */

function renderField_URL() {
global $jerelabs_ccb_options;
echo "<input id='setting_URL' name='jerelabs_ccb_options[ccb_url]' size='40' type='text' value='{$jerelabs_ccb_options['ccb_url']}' /><br />e.g. https://churchname.ccbchurch.com/";
}

function renderField_APIURL() {
global $jerelabs_ccb_options;
echo "<input id='setting_APIURL' name='jerelabs_ccb_options[api_url]' size='40' type='text' value='{$jerelabs_ccb_options['api_url']}' /><br />e.g. http://churchname.ccbchurch.com/api.php";
}

function renderField_Username() {
global $jerelabs_ccb_options;
echo "<input id='setting_api_username' name='jerelabs_ccb_options[api_username]' size='15' type='text' value='{$jerelabs_ccb_options['api_username']}' />";
}

function renderField_Password() {
global $jerelabs_ccb_options;
echo "<input id='setting_api_password' name='jerelabs_ccb_options[api_password]' size='15' type='password' value='{$jerelabs_ccb_options['api_password']}' />";
}

function renderField_Reset() {
global $jerelabs_ccb_options;
$checked = '';
if($jerelabs_ccb_options['setting_reset']=='on') { $checked = ' checked="checked" '; }
echo "<input ".$checked." id='setting_reset' name='jerelabs_ccb_options[setting_reset]' type='checkbox' /><br />This will reset all options to their default setting if you deactivate, then reactivate the plugin.";
}

function renderField_Debug() {
global $jerelabs_ccb_options;
$checked = '';
if($jerelabs_ccb_options['setting_debug']=='on') { $checked = ' checked="checked" '; }
echo "<input ".$checked." id='setting_debug' name='jerelabs_ccb_options[setting_debug]' type='checkbox' /><br />Outputs debug messages to logged on users.  Limited use mainly for the developer.";
}

function renderField_Cache() {
global $jerelabs_ccb_options;
$checked = '';
if($jerelabs_ccb_options['setting_cache']=='on') { $checked = ' checked="checked" '; }
echo "<input ".$checked." id='setting_debug' name='jerelabs_ccb_options[setting_cache]' type='checkbox' /><br />You're strongly encouraged to enable caching once you've fully configured this plugin.  The cache refreshes every 6 hours.";
}


function validateSettings_General($input)
{
  global $jerelabs_ccb_options;
  $outVars = $input;
      $outVars['ccb_url'] = rtrim($input['ccb_url'],"/");    

      $outVars['api_url'] = rtrim($input['api_url'],"/");
      $outVars['api_url'] = rtrim($input['api_url'],"?");
      $outVars['api_url'] = rtrim($input['api_url'],"/");

/*

    if($outVars['setting_cache'] != 'on')
      {
        $outVars['setting_cache'] = '';     
      }

    if($outVars['setting_reset'] != 'on')
      {
        $outVars['setting_reset'] = '';     
      }

  
    if($outVars['setting_debug'] != 'on')
      {
        $outVars['setting_debug'] = '';     
      }
*/

  return $outVars;
}

function validateSettings_XSL($input)
{
  return $input;
}

?>

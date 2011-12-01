<?php
/*
Plugin Name: CCB Group List by Jeremy Johnson
Plugin URI: http://www.jerelabs.com/plugins/ccb-group-list
Description: CCB Group List by Jeremy Johnson
Version: 0.3 Alpha
Author: Jeremy Johnson
Author URI: http://www.jerelabs.com

Licensed Under: http://creativecommons.org/licenses/by-nc/3.0/
*/

//error_reporting(E_ALL);

$jerelabs_ccb_plugin_file = WP_PLUGIN_DIR . '/jerelabs_ccb_groups/jerelabs-ccb-groups-main.php';
//$plugin_path = plugin_dir_path($jerelabs_ccb_plugin_file);
$jerelabs_ccb_options = get_option('jerelabs_ccb_options');

$jerelabs_ccb_xsl_config = get_option('jerelabs_ccb_xsl_config');
$jerelabs_ccb_cache_hours = 4;

add_shortcode("ccb-api", "shortcodeHandler");
add_shortcode("ccb-form", "shortcodeHandler_Form");

register_activation_hook($jerelabs_ccb_plugin_file, 'jerelabs_ccb_add_defaults_fn');

function jerelabs_ccb_add_defaults_fn() {
  global $jerelabs_ccb_options;
  global $jerelabs_ccb_xsl_config;

  if(($jerelabs_ccb_options['setting_reset']=='true')||(!is_array($jerelabs_ccb_options))) {
    $arrGeneral = array("ccb_url" => '', "api_url" => '',"api_username" => '', "api_password" => '', "setting_reset" => '', "setting_cache"=>'on',"setting_debug"=>'');
    update_option('jerelabs_ccb_options', $arrGeneral);
  }

  if(($jerelabs_ccb_options['setting_reset']=='true')||(!is_array($jerelabs_ccb_xsl_config))) {
    $xsl = file_get_contents(WP_PLUGIN_DIR . '/jerelabs_ccb_groups/group_profile_list.xsl');
    $arrXSL[0] = array("name" => 'SmallGroupList',"srv" => 'group_profiles', "xsl" =>  $xsl);
    update_option('jerelabs_ccb_xsl_config', $arrXSL);
  }


  if(($jerelabs_ccb_options['setting_reset']=='true')||(!is_array($jerelabs_ccb_xsl_config))) {
    $xsl = file_get_contents(WP_PLUGIN_DIR . '/jerelabs_ccb_groups/public_calendar_listing.xsl');
    $arrXSL[1] = array("name" => 'CalendarList',"srv" => 'public_calendar_listing', "xsl" =>  $xsl);
    update_option('jerelabs_ccb_xsl_config', $arrXSL);
  }
  

  if(($jerelabs_ccb_options['setting_reset']=='true')||(!is_array($jerelabs_ccb_xsl_config))) {
    //$xsl = file_get_contents(WP_PLUGIN_DIR . '/jerelabs_ccb_groups/group_profile_list.xsl');
    $xsl = '';
    $arrXSL[2] = array("name" => '',"srv" => '', "xsl" =>  $xsl);
    update_option('jerelabs_ccb_xsl_config', $arrXSL);
  }
  

  if(($jerelabs_ccb_options['setting_reset']=='true')||(!is_array($jerelabs_ccb_xsl_config))) {
    //$xsl = file_get_contents(WP_PLUGIN_DIR . '/jerelabs_ccb_groups/group_profile_list.xsl');
    $xsl = '';
    $arrXSL[3] = array("name" => '',"srv" => '', "xsl" =>  $xsl);
    update_option('jerelabs_ccb_xsl_config', $arrXSL);
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


  if($content == '')
  { 
    $content = jerelabs_getContentFromURL($url);
    try {
      if(file_put_contents($outputFile.'.xml',$content)==FALSE)
      {
        jerelabs_errorMessage("Unable to write raw xml file, is tmp directory writeable?","");
      }
    } catch (Exception $e) {
      jerelabs_errorMessage("Unable to write raw xml file, is tmp directory writeable?",$e->getMessage());
    }
    if($fn) { $content = $fn($content,$fn_args); }
    
    $content.= '';

    try {
      if(file_put_contents($outputFile,$content)==FALSE)
      {
        jerelabs_errorMessage("Unable to write formatted cache file, is tmp directory writeable?","");
      }
    } catch (Exception $e) {
      jerelabs_errorMessage("Unable to write formatted cache file, is tmp directory writeable?",$e->getMessage());
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

function shortcodeHandler($atts) {
  //run function that actually does the work of the plugin

    if(!is_array($atts))
    {
      jerelabs_errorMessage("Name parameter in shortcode is empty or missing.","");
      return "";
    }

    $fixedAtts = array_change_key_case($atts,CASE_LOWER);
    debugMessage("Name: " . $fixedAtts['name']);

    if($fixedAtts['name'] == "")
    {
      jerelabs_errorMessage("Name parameter in shortcode is empty or missing.","");
      return "";
    }

    $xslID = findXSLName($fixedAtts['name']);

    if($xslID < 0)
    {
      jerelabs_errorMessage("Cannot find definition for '".$fixedAtts['name']."'.  Check the shortcode or the plugin settings.","ID: " . $xslID);
      return "";
    }

    //Pass additional parameters as API parameters
    $ccbAtts = $atts;
    unset($ccbAtts['name']);

    //Fix params
    if(array_key_exists('date_start',$ccbAtts))
      if($ccbAtts['date_start']=='today')
        $ccbAtts['date_start'] = date('Y-m-d');

    

    if(array_key_exists('num_days',$ccbAtts))
      {
        if(!is_numeric($ccbAtts['num_days']))
        {
          jerelabs_errorMessage("num_days parameter is not a number","");
          return "";
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
      }

    $demolph_output = makeCCBCall($xslID, $ccbAtts);
  
  //send back text to replace shortcode in post
  return $demolph_output;
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

function buildCCBURL($id, $atts)
{
	global $jerelabs_ccb_xsl_config;
  global $jerelabs_ccb_options;

  if(!is_numeric($id))
  {
    jerelabs_errorMessage("Invalid ID passed to buildCCBURL","");
    return "";
  }

  if( $jerelabs_ccb_options['api_url']=="")
  {
    jerelabs_errorMessage("API URL is is empty");
    return "";
  }

  if($jerelabs_ccb_xsl_config[$id]['srv']=="")
  {
    jerelabs_errorMessage("XSL Service Paramater is is empty");
    return "";
  }

	$finalURL = $jerelabs_ccb_options['api_url'] . "?srv=" . $jerelabs_ccb_xsl_config[$id]['srv'];

  if(count($atts)>0)
  {
    foreach($atts as $key => $value)
    {
      $finalURL = $finalURL . "&" . $key . "=" . urlencode($value);
    }
  
  }
	return $finalURL;
}

function makeCCBCall($id, $atts)
{
  global $jerelabs_ccb_xsl_config;
  global $jerelabs_ccb_options;
  global $jerelabs_ccb_cache_hours;

  if($jerelabs_ccb_xsl_config[$id]['srv']=="")
  {
    jerelabs_errorMessage("XSL Service Configuration Option is is empty");
    return "";
  }

  if($jerelabs_ccb_xsl_config[$id]['xsl']=="")
  {
    jerelabs_errorMessage("XSL Definition Configuration Option is is empty");
    return "";
  }

  if(!is_numeric($id))
  {
    jerelabs_errorMessage("Invalid ID passed to makeCCBCall","");
    return "";
  }

  $cacheFileName =  $jerelabs_ccb_xsl_config[$id]['srv'].'-'.$id.'.html';
  
  $ccbURL = buildCCBURL($id,$atts);
  
  if($ccbURL == "")
    {
      jerelabs_errorMessage("Error determining CCB URL");
      return "";
    }

  $xsl = $jerelabs_ccb_xsl_config[$id]['xsl'];

  debugMessage("XSL ID: " . $id);

  try {
    $html_output = jerelabs_get_content($cacheFileName, $ccbURL,$jerelabs_ccb_cache_hours,'processCCBData',array('file'=>$cacheFileName, 'XSL'=>$xsl));
  } catch (Exception $e) {
    jerelabs_errorMessage("Error retrieving content",$e->getMessage());
      return "";
  }


  return $html_output;
}

function processCCBData($content, $args)
{
  $html_output = '';
  global $jerelabs_ccb_options;

  $use_errors = libxml_use_internal_errors(true);

  debugMessage("XML: ". strlen($content));
  debugMessage("XSL: ". strlen($args['XSL']));

  if(strlen($content) < 1)
  {
    jerelabs_errorMessage("Error in processCCBData, no XML retrieved.");
    return "";
  }

  if(strlen($args['XSL']) < 1)
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
    $xsl = stringToDOMDoc($args['XSL']); 
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

  add_settings_section('section_XSL1', 'XSL Tempate 1', 'renderSectionHeading_XSL', 'jerelabs_ccb');
  add_settings_field('setting_xsl1_name','Name:','renderField_xsl1_name','jerelabs_ccb','section_XSL1');
  add_settings_field('setting_xsl1_srv','Service:','renderField_xsl1_srv','jerelabs_ccb','section_XSL1');
  add_settings_field('setting_xsl1_xsl','XSL:','renderField_xsl1_xsl','jerelabs_ccb','section_XSL1');

  add_settings_section('section_XSL2', 'XSL Tempate 2', 'renderSectionHeading_XSL', 'jerelabs_ccb');
  add_settings_field('setting_xsl2_name','Name:','renderField_xsl2_name','jerelabs_ccb','section_XSL2');
  add_settings_field('setting_xsl2_srv','Service:','renderField_xsl2_srv','jerelabs_ccb','section_XSL2');
  add_settings_field('setting_xsl2_xsl','XSL:','renderField_xsl2_xsl','jerelabs_ccb','section_XSL2');

  add_settings_section('section_XSL3', 'XSL Tempate 3', 'renderSectionHeading_XSL', 'jerelabs_ccb');
  add_settings_field('setting_xsl3_name','Name:','renderField_xsl3_name','jerelabs_ccb','section_XSL3');
  add_settings_field('setting_xsl3_srv','Service:','renderField_xsl3_srv','jerelabs_ccb','section_XSL3');
  add_settings_field('setting_xsl3_xsl','XSL:','renderField_xsl3_xsl','jerelabs_ccb','section_XSL3');
  
  add_settings_section('section_XSL4', 'XSL Tempate 4', 'renderSectionHeading_XSL', 'jerelabs_ccb');
  add_settings_field('setting_xsl4_name','Name:','renderField_xsl4_name','jerelabs_ccb','section_XSL4');
  add_settings_field('setting_xsl4_srv','Service:','renderField_xsl4_srv','jerelabs_ccb','section_XSL4');
  add_settings_field('setting_xsl4_xsl','XSL:','renderField_xsl4_xsl','jerelabs_ccb','section_XSL4');

}

function renderSectionHeading_General()
{
  echo '<p>Main description of this section here.</p>';
}

function renderSectionHeading_XSL() {
  echo '<span class="section_desc">Configure the API call and XSLT used to format the output</span>';
}

function renderSectionHeadingCCB() {
echo '<span class="section_desc">Enter the API information from <strong>Settings</strong> | <strong>API</strong> on your CCB website</span>';
}



/* XLS 1 */

function renderField_xsl1_name()
{
  global $jerelabs_ccb_xsl_config;
  echo "<input id='setting_xsl1_name' name='jerelabs_ccb_xsl_config[0][name]' size='40' type='text' value='{$jerelabs_ccb_xsl_config[0]['name']}'><br />A name used to reference this in your post.  Eg. [ccb-api name='NAME']";
  
}

function renderField_xsl1_srv(){
  global $jerelabs_ccb_xsl_config;
  echo "<input id='setting_xsl1_srv' name='jerelabs_ccb_xsl_config[0][srv]' size='40' type='text' value='{$jerelabs_ccb_xsl_config[0]['srv']}'><br />Name of the CCB API service.";
}

function renderField_xsl1_xsl()
{
  global $jerelabs_ccb_xsl_config;
  echo "<textarea id='setting_xsl1_xsl' name='jerelabs_ccb_xsl_config[0][xsl]' rows=15 cols=80>{$jerelabs_ccb_xsl_config[0]['xsl']}</textarea>";
}

/* XLS 2 */

function renderField_xsl2_name()
{
  global $jerelabs_ccb_xsl_config;
  echo "<input id='setting_xsl2_name' name='jerelabs_ccb_xsl_config[1][name]' size='40' type='text' value='{$jerelabs_ccb_xsl_config[1]['name']}'><br />A name used to reference this in your post.  Eg. [ccb-api name='NAME']";
  
}

function renderField_xsl2_srv(){
  global $jerelabs_ccb_xsl_config;
  echo "<input id='setting_xsl2_srv' name='jerelabs_ccb_xsl_config[1][srv]' size='40' type='text' value='{$jerelabs_ccb_xsl_config[1]['srv']}'><br />Name of the CCB API service.";
}

function renderField_xsl2_xsl()
{
  global $jerelabs_ccb_xsl_config;
  echo "<textarea id='setting_xsl2_xsl' name='jerelabs_ccb_xsl_config[1][xsl]' rows=15 cols=80>{$jerelabs_ccb_xsl_config[1]['xsl']}</textarea>";
}

/* XLS 3 */

function renderField_xsl3_name()
{
  global $jerelabs_ccb_xsl_config;
  echo "<input id='setting_xsl3_name' name='jerelabs_ccb_xsl_config[2][name]' size='40' type='text' value='{$jerelabs_ccb_xsl_config[2]['name']}'><br />A name used to reference this in your post.  Eg. [ccb-api name='NAME']";
  
}

function renderField_xsl3_srv(){
  global $jerelabs_ccb_xsl_config;
  echo "<input id='setting_xsl3_srv' name='jerelabs_ccb_xsl_config[2][srv]' size='40' type='text' value='{$jerelabs_ccb_xsl_config[2]['srv']}'><br />Name of the CCB API service.";
}

function renderField_xsl3_xsl()
{
  global $jerelabs_ccb_xsl_config;
  echo "<textarea id='setting_xsl3_xsl' name='jerelabs_ccb_xsl_config[2][xsl]' rows=15 cols=80>{$jerelabs_ccb_xsl_config[2]['xsl']}</textarea>";
}

/* XLS 4 */

function renderField_xsl4_name()
{
  global $jerelabs_ccb_xsl_config;
  echo "<input id='setting_xsl4_name' name='jerelabs_ccb_xsl_config[3][name]' size='40' type='text' value='{$jerelabs_ccb_xsl_config[3]['name']}'><br />A name used to reference this in your post.  Eg. [ccb-api name='NAME']";
  
}

function renderField_xsl4_srv(){
  global $jerelabs_ccb_xsl_config;
  echo "<input id='setting_xsl4_srv' name='jerelabs_ccb_xsl_config[3][srv]' size='40' type='text' value='{$jerelabs_ccb_xsl_config[3]['srv']}'><br />Name of the CCB API service.";
}

function renderField_xsl4_xsl()
{
  global $jerelabs_ccb_xsl_config;
  echo "<textarea id='setting_xsl4_xsl' name='jerelabs_ccb_xsl_config[3][xsl]' rows=15 cols=80>{$jerelabs_ccb_xsl_config[3]['xsl']}</textarea>";
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

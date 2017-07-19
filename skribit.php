<?php
/*
Plugin Name: Skribit
Plugin URI: http://skribit.com/wordpress
Description: Plugin that adds the Skribit widget to your sidebar.
Version: 0.5.1
Author: Calvin Yu
Author URI: http://blog.codeeg.com
*/
/*  Copyright 2009  Calvin Yu (email : calvin@skribit.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( !class_exists("SkribitPlugin") ) {
  class SkribitPlugin {
    var $adminOptionsName = "SkribitPluginAdminOptions";
    var $widgetId = "skribitWidgetContainer";
    var $baseUrl = "http://skribit.com";
    var $baseScriptUrl = "http://assets.skribit.com";

    // constructor
    function SkribitPlugin() { }
    
    function init() {
      $this->getAdminOptions();
    } # end init()
    
    function getAdminOptions() {
      $skribitDefaultOptions = array("blog_code" => "", "lightbox" => 0);
      $skribitOptions = get_option($this->adminOptionsName);
      
      if ( !empty($skribitOptions) ) {
        foreach ( $skribitOptions as $key => $option )
          $skribitDefaultOptions[$key] = $option;
      }
      
      update_option($this->adminOptionsName, $skribitDefaultOptions);
      
      return $skribitDefaultOptions;
    } # end getAdminOptions()
    
    function printAdminPage(){
      $skribitOptions = $this->getAdminOptions();
      
      if ( isset($_POST["update_skribitSettings"]) ) {
    		$oldBlogCode = $skribitOptions["blog_code"];

        if ( isset($_POST["skribitBlogCode"]) ) {
    		  $skribitOptions["blog_code"] = apply_filters("content_pre_save", $_POST["skribitBlogCode"]);
    		  if ( $oldBlogCode != $skribitOptions["blog_code"] && isset($skribitOptions["slug"]) )
      			$skribitOptions["slug"] = null;
    		}
	
  	    if ( isset($_POST["skribitBlogSlug"]) )
  	      $skribitOptions["slug"] = apply_filters("content_pre_save", $_POST["skribitBlogSlug"]);

  	    $skribitOptions["lightbox"] = (isset($_POST["skribitLightBox"]) && $_POST["skribitLightBox"] == "on");

        update_option($this->adminOptionsName, $skribitOptions);
?>
        <div class="updated">
          <p><strong><?php _e("Settings Updated.", "skribit");?></strong></p>
        </div>
        <?php

    		$blogCode     = $skribitOptions["blog_code"];
    		$blogSlug     = $skribitOptions["slug"];
    		$lightBox     = $skribitOptions['lightbox'];

      } else {
    		$blogCode     = isset($_GET["blog_code"]) ? $_GET["blog_code"] : $skribitOptions["blog_code"];
    		$blogSlug     = isset($_GET["slug"])      ? $_GET["slug"]      : $skribitOptions["slug"];
        $lightBox     = $skribitOptions["lightbox"];
  	  } ?>
      
      <div class="wrap">
        <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
          <h2>Skribit Options</h2> 
          <table class="form-table">
            <tr valign="top">
              <th scope="row"><label for="skribitBlogCode">Blog Code</label></th>
              <td>
                <input type="text" name="skribitBlogCode" id="skribitBlogCode" size="35"
                     value="<?php _e(apply_filters("format_to_edit", $blogCode), "skribit") ?>"/>
                <a href="<?php echo $this->baseUrl ?>/blogs/get_code?r=<?php echo urlencode("http://".$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]); ?>&amp;site_name=<?php _e(get_bloginfo('name'));?>">Get Blog Code</a>
              </td>
            </tr>

            <?php if ( isset($blogSlug) ) { ?>
            <tr valign="top">
              <th scope="row"><label for="skribitBlogSlug">Slug</label></th>
              <td>
                <?php _e($blogSlug, "skribit") ?>
                <input type="hidden" name="skribitBlogSlug" id="skribitBlogSlug"
                     value="<?php _e(apply_filters("format_to_edit", $blogSlug), "skribit") ?>"/>
              </td>
            </tr>
            <tr valign="top">
              <th scope="row">
              </th>
              <td>
                <label for="skribitLightBox">
                  <input class="checkbox" type="checkbox" name="skribitLightBox" id="skribitLightBox" value="on"
                      <?php _e($lightBox ? ' checked=""' : '')?> />
                  Show LightBox Widget
                </label>
              </td>
            </tr>
            <?php } ?>
          </table>
          <p class="submit">
            <input type="submit" name="update_skribitSettings" 
                 value="<?php _e("Update Settings", "skribit") ?>" />
            </p>
        </form>
      </div>
      
      <?php
    } # end printAdminPage()
    
    function skribitLoaded() {
      register_sidebar_widget(array('Skribit', 'widgets'), 'Skribit_Sidebar');
      register_widget_control(array('Skribit', 'widgets'), 'skribit_widget_control');
    } # end skribitLoaded()
      
    function writeWidgetContainer($args){
      extract($args);

      $defaults = array('title' => 'Skribit Suggestions', 'no_css' => false);
      $options = get_option('widget_skribit');

      foreach ( $defaults as $key => $value )
        if ( !isset($options[$key]) )
          $options[$key] = $defaults[$key];

      $title = $options['title'];
      $no_css = $options['no_css'];

      $options = $this->getAdminOptions();
      $blog_code = $options["blog_code"];

      echo $before_widget . $before_title . $title . $after_title;
      echo "<div id='".$this->widgetId."'></div>";
      echo "<script type='text/javascript' src='".$this->baseScriptUrl."/javascripts/SkribitWidget.js?renderTo=".$this->widgetId."&amp;blog=".$blog_code."&amp;noCSS=".$no_css."'></script>";
      echo "<noscript>Sorry, but the Skribit widget only works on browsers that support JavaScript.  ";
      if ($options["slug"])
        echo "<a href=\"http://skribit.com/blogs/".$options["slug"]."\">View suggestions for this blog here.</a>";
      echo "</noscript>";
      echo $after_widget;
    } # end writeWidgetContainer()

    function writeLightBox(){
  	  $options = $this->getAdminOptions();
  	  if ( isset($options['slug']) && $options['lightbox'] ) {
        echo "<style type='text/css'>@import url('".$this->baseScriptUrl."/stylesheets/SkribitSuggest.css');</style>";
        echo "<script src='".$this->baseScriptUrl."/javascripts/SkribitSuggest.js' type='text/javascript'></script>";
        echo "<script type='text/javascript' charset='utf-8'>";
        echo "var skribit_settings = {};";
        echo "skribit_settings.placement = 'right';";
        echo "skribit_settings.color = '#333333';";
        echo "skribit_settings.text_color = 'white';";
        echo "skribit_settings.distance_vert = '20%';";
        echo "SkribitSuggest.suggest('".$this->baseUrl."/lightbox/".$options['slug']."', skribit_settings);";
        echo "</script>";
  	  }
  	} # end writeLightBox
  }
}

if ( class_exists("SkribitPlugin") )
  $skribitPlugin = new SkribitPlugin(); 

if ( isset($skribitPlugin) ) {
  add_action("activate_skribit/skribit.php",  array(&$skribitPlugin, "init"));
  add_action("admin_menu", "Skribit_AdminPanel");
  add_action("plugins_loaded", array(&$skribitPlugin, "skribitLoaded"));
  add_action('wp_head', 'Skribit_lightBox');
}

if ( !function_exists("Skribit_AdminPanel") ) {
  function Skribit_AdminPanel() {
    global $skribitPlugin;
    if(!isset($skribitPlugin)) return;
    
    if ( function_exists("add_options_page") )
      add_options_page("Skribit", "Skribit", 9, basename(__FILE__), array(&$skribitPlugin, "printAdminPage"));
  }
}

if ( !function_exists("skribit_widget_control") ) {
  function skribit_widget_control() {
    $options = get_option('widget_skribit');

    if ( !is_array($options) )
      $options = array('title'=>'Skribit Suggestions');

    if ( $_POST['skribit-submit'] ) {
      // Remember to sanitize and format use input appropriately.
      $options['title'] = strip_tags(stripslashes($_POST['skribit-title']));

      $options['no_css'] = ($_POST['skribit-nocss'] == 'on');
      update_option('widget_skribit', $options);
    }

    // Be sure you format your options to be valid HTML attributes.
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    $noCSS = $options['no_css'];
    
    ?>

    <p><label for="skribit-title"><?php _e('Title:');?> <input id="skribit-title" class="widefat" name="skribit-title" type="text" value="<?php _e($title);?>" /></label></p>
    <p><label for="skribit-nocss"><input class="checkbox" type="checkbox" name="skribit-nocss" value="on"<?php _e($noCSS ? ' checked=""' : '')?>/> <?php _e('Disable Skribit CSS');?></label></p>
    <input type="hidden" id="skribit-submit" name="skribit-submit" value="1" />

    <?php
  } # end skribit_widget_control()
}

if ( !function_exists("Skribit_Sidebar") ) {
  function Skribit_Sidebar($args){
    global $skribitPlugin;
    if(!isset($skribitPlugin)) return;
    
    return $skribitPlugin->writeWidgetContainer($args);   
  }
}

if ( !function_exists("Skribit_lightBox") ) {
  function Skribit_lightBox() {
	global $skribitPlugin;
    if (!isset($skribitPlugin)) return;
    $skribitPlugin->writeLightBox();   
  }
}
?>

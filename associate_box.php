<?php


/*
Plugin Name: Associatebox
Plugin URI: http://www.worldwidewaiting.de/wordpress/
Description: Product API Wordpress Plugin for Amazon
Version: 1.5
Author: Martin Stemberg
Author URI: 
License: GPLv2 or later
Text Domain: associatebox
*/


require_once(dirname(__FILE__)."/associatebox_widget.php");


//ACTIONS
add_action('widgets_init',					'associatebox_amazon_load_widget');
add_action( 'plugins_loaded', 'associatebox_load_textdomain' );
add_action( 'admin_enqueue_scripts', 'associatebox_load_custom_wp_admin_style' );
add_action( 'admin_enqueue_scripts', 'associatebox_load_custom_wp_admin_style' );
add_action('admin_menu', 'associatebox_page_create');
add_action( 'add_meta_boxes', 'associatebox_add_meta_box' );
add_action("save_post", "associatebox_save_custom_meta_box", 10, 3);
add_filter( 'the_content', 'associatebox_append' );


if ( ! is_admin() ) {

add_filter( 'sidebars_widgets', 'associatebox_my_disable_widget' );
}


//Widget registrieren
function associatebox_amazon_load_widget() {

	register_widget( 'Associatebox_Widget' );
}
//Textdomain - Sprachfiles
function associatebox_load_textdomain() {
  $wurst=load_plugin_textdomain( 'associatebox-for-amazon', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' ); 

}
add_action( 'plugins_loaded', 'associatebox_load_textdomain' );

//ADMIN CSS

function associatebox_load_custom_wp_admin_style() {
        wp_register_style( 'associatebox_css', plugins_url() . '/associatebox-for-amazon/css/associatebox.css', false, '1.0.0' );
        wp_enqueue_style( 'associatebox_css' );
}

function associatebox_add_meta_box() {

$args = array(
   'public' => true,
);
	$screens = get_post_types($args);
	//var_dump($screens);

	foreach ( $screens as $screen ) {

		add_meta_box( 
        'associatebox-for-amazon',
        __( 'AssociateBox' ),
        'associatebox_meta_box_callback',
        $screen,
        'normal',
        'default'
    );

	}
}


//Meta-Box
function associatebox_meta_box_callback( $post ) {
wp_nonce_field(basename(__FILE__), "meta-box-nonce");

?>

 <div>
           
           <?php echo _e( '<b>Title for Widget on this Page:</b>', 'associatebox-for-amazon' ); ?>

<input name="associatebox_title" type="text" value="<?php echo get_post_meta($post->ID, "associatebox_title", true); ?>">



<?php 

echo '<div>';
echo _e( '<b>Show results in widget or under the content?</b> ', 'associatebox-for-amazon' );
$placement=get_post_meta($post->ID, "associatebox_placement", true);

if (empty($placement) OR $placement=="widget") {
$checkbox22='checked="checked"';
} else {
$checkbox11='checked="checked"';
}
echo '<input type="radio" id="placement" '.$checkbox22.' name="associatebox_placement" value="widget"> <label for="mc">'._e( 'Widget ', 'associatebox-for-amazon' ).'</label>';
echo '<input type="radio" id="placement" '.$checkbox11.' name="associatebox_placement" value="under"> <label for="mc">'._e( 'Under the content ', 'associatebox-for-amazon' ).'</label>';
echo "</div>";

echo '<div>';
echo _e( '<b>Shuffle results?</b> ', 'associatebox-for-amazon' );
$shuffle=get_post_meta($post->ID, "associatebox_shuffle", true);

if (empty($shuffle) OR $shuffle=="no") {
$checkbox2='checked="checked"';
} else {
$checkbox1='checked="checked"';
}
echo '<input type="radio" id="shuffle" '.$checkbox1.'name="associatebox_shuffle" value="shuffle"> <label for="mc">'._e( 'Yes ', 'associatebox-for-amazon' ).'</label>';
echo '<input type="radio" id="shuffle" '.$checkbox2.' name="associatebox_shuffle" value="no"> <label for="mc">'._e( 'No ', 'associatebox-for-amazon' ).'</label>';
echo "</div>";
echo "<div>";
$host=get_post_meta($post->ID, "associatebox_host", true);

//Standard-Locale
if (empty($host)) {
$host=get_option('associatebox_standardhost');;
}
echo _e( '<b>Amazon Server:</b>', 'associatebox-for-amazon' );
$hostarray = array(
"de"=>"amazon.de (DE)",
"com"=>"amazon.com (US)",
"co.uk"=>"amazon.co.uk (GB)",
"fr"=>"amazon.fr (FR)",
"es"=>"amazon.es (ES)",

);
echo'<select class="associatebox_host" name="associatebox_host" >';
foreach ($hostarray as $key => $wert) {
if ($key==$host) {
$selectedhost='selected="selected"';
} else {
$selectedhost="";
}
echo "<option ".$selectedhost." value=".$key.">".$wert."</option>";
}
echo "</select>";
echo "</div>";

?>
<?php
if (empty($art) OR $art=="empty") {

$checked0="checked";
}

?>

<div class="boxempty"><?php echo _e( '<b>No Amazon-Articles</b>', 'associatebox-for-amazon' ); ?>
<input type="radio" class="associatebox_art" name="associatebox_art" <?php echo $checked0; ?> value="empty">
</div>

<div style="clear:both;"></div>
<div class="abstandmodi"></div>




<div class="boxasins"><?php echo _e( '<b>Search for ASINs:</b>', 'associatebox-for-amazon' ); ?>



<?php
$art=get_post_meta($post->ID, "associatebox_art", true);

if ($art=="asin") {

$checked1="checked";
}
?>

<input id="asin" type="radio" class="associatebox_art" name="associatebox_art" <?php echo $checked1; ?> value="asin">
<div style="clear:both;"></div>
<?php echo '<div class="abstandheadline"><label for="associatebox_asins">';
	echo _e( '<b>Amazon-Asins, separated by commas:</b>', 'associatebox-for-amazon' );
	echo '</label></div>';


$value = get_post_meta( $post->ID, 'associatebox_asin', true );
echo '<textarea id="associatebox_asin" name="associatebox_asin" class="associateboxtextarea">' . esc_attr( $value).'</TEXTAREA>';
echo _e( '<b>Amount Articles shown:</b>', 'associatebox-for-amazon' );
$associatebox_multiamount=get_post_meta( $post->ID, 'associatebox_multiamount', true );


if(empty($associatebox_multiamount)) {
$associatebox_multiamount=1;
}

echo'<select class="associatebox_amount" name="associatebox_multiamount" >';


for ($i=1;$i<=10;$i++) {

if ($i==$associatebox_multiamount) {

$selected="selected";
} else {

$selected="";
}
echo "<option ".$selected." value=".$i.">".$i."</option>";
}

echo "</select>";
?>


</div>


<div class="abstandmodi"></div>
<div class="boxbrowse"><?php echo _e( '<b>Items in BrowseNode:</b>', 'associatebox-for-amazon' ); ?>
<?php
$checked2="";
$art=get_post_meta($post->ID, "associatebox_art", true);

if ($art=="browse") {

$checked2="checked";
}
?>

<input id="browse" class="associatebox_art" type="radio" name="associatebox_art" <?php echo $checked2; ?> value="browse" >
<div style="clear:both;"></div>
<?php
$valuebrowse=get_post_meta($post->ID, "associatebox_browsenode", true);

?>
<div><label for="BrowseNode"><?php _e( 'BrowseNodeID','associatebox-for-amazon' ); ?></label>&nbsp;
    <input type="text" name="associatebox_browsenode" id="associatebox_browsenode" value="<?php echo $valuebrowse; ?>"></div>
<?php
$indexarray = array(
    "Books" => esc_html__( 'Books','associatebox-for-amazon' ),
  "DVD" => esc_html__( 'DVD','associatebox-for-amazon' ),
  "Music" => esc_html__( 'Music','associatebox-for-amazon' ),
"VideoGames" => esc_html__( 'VideoGames','associatebox-for-amazon' ),
"Electronics" => esc_html__( 'Electronics','associatebox-for-amazon' ),
"Automotive" => esc_html__( 'Automotive','associatebox-for-amazon' ),
"Baby" => esc_html__( 'Baby','associatebox-for-amazon' ),
"Tools" => esc_html__( 'Tools','associatebox-for-amazon' ),
"PCHardware" => esc_html__( 'PcHardware','associatebox-for-amazon' ),
"HealthPersonalCare" => esc_html__( 'HealthPersonalCare','associatebox-for-amazon' ),
"Kitchen" => esc_html__( 'Kitchen','associatebox-for-amazon' ),
"Software" => esc_html__( 'Software','associatebox-for-amazon' ),
"Toys" => esc_html__( 'Toys','associatebox-for-amazon' ),
"Beauty" => esc_html__( 'Beauty','associatebox-for-amazon' ),
"MP3Downloads" => esc_html__( 'MP3Downloads','associatebox-for-amazon' ),
"MusicalInstruments" => esc_html__( 'MusicalInstruments','associatebox-for-amazon' ),
"Classical" => esc_html__( 'Classical','associatebox-for-amazon' ),
"Grocery" => esc_html__( 'Grocery','associatebox-for-amazon' ),
"Watches" => esc_html__( 'Watches','associatebox-for-amazon' ),
"Magazines" => esc_html__( 'Magazines','associatebox-for-amazon' ),
"Shoes" => esc_html__( 'Shoes','associatebox-for-amazon' ),
"HomeGarden" => esc_html__( 'HomeGarden','associatebox-for-amazon' ),
"GiftCards" => esc_html__( 'GiftCards','associatebox-for-amazon' ),
"PetSupplies" => esc_html__( 'PetSupplies','associatebox-for-amazon' ),
"Photo" => esc_html__( 'Photo','associatebox-for-amazon' ),
"Appliances" => esc_html__( 'Appliances','associatebox-for-amazon' ),
"Apparel" => esc_html__( 'Apparel','associatebox-for-amazon' ),
"Lighting" => esc_html__( 'Lighting','associatebox-for-amazon' ),
"ForeignBooks" => esc_html__( 'ForeignBooks','associatebox-for-amazon' ),
"Pantry" => esc_html__( 'Pantry','associatebox-for-amazon' ),
"MobileApps" => esc_html__( 'MobileApps','associatebox-for-amazon' ),
"OfficeProducts" => esc_html__( 'OfficeProducts','associatebox-for-amazon' ),
"KindleStore" => esc_html__( 'KindleStore','associatebox-for-amazon' ),
"Luggage" => esc_html__( 'Luggage','associatebox-for-amazon' ),
"Jewelry" => esc_html__( 'Jewelry','associatebox-for-amazon' ),
"Industrial" => esc_html__( 'Industrial','associatebox-for-amazon' ),


);
//var_dump($indexarray);


$searchindex=get_post_meta($post->ID, "associatebox_mysearchindex", true);


echo _e( 'SearchIndex: ', 'associatebox-for-amazon' );

echo '<select class="associateboxsearchindex" name="associatebox_searchindex">';



foreach ($indexarray as $k => $v) {


if ($k==$searchindex) {
$selected3='selected="selected"';
} else {
$selected3="";
}
   echo "<option ".$selected3." value=".$k.">".$v."</option>";
}
echo "</select>";

$associatebox_multiamount2=get_post_meta( $post->ID, 'associatebox_multiamount2', true );


if(empty($associatebox_multiamount2)) {

$associatebox_multiamount2=1;
}
echo '<div>';
echo _e( '<b>Amount Articles shown:</b>', 'associatebox-for-amazon' );
echo'<select class="associatebox_amount" name="associatebox_multiamount2">';


for ($o=1;$o<=10;$o++) {
if ($o==$associatebox_multiamount2) {

$selected2="selected";
} else {

$selected2="";
}
echo "<option ".$selected2." value=".$o.">".$o."</option>";
}

echo "</select></div>";

$checked3="";
$art=get_post_meta($post->ID, "associatebox_art", true);

if ($art=="keyword") {

$checked3="checked";
}
?>
</div>

<div class="abstandmodi"></div>
<div class="boxkeyword"><?php echo _e( '<b>Search for Keyword:</b>', 'associatebox-for-amazon' ); ?>
<input id="keyword" class="associatebox_art" type="radio" name="associatebox_art" <?php echo $checked3; ?> value="keyword" >
<div style="clear:both;"></div>
<div><label for="Keyword"><?php _e( 'Keyword','associatebox-for-amazon' ); ?></label>&nbsp;
<?php
$valuekeyword=get_post_meta($post->ID, "associatebox_keyword", true);
?>
    <input type="text" name="associatebox_keyword" id="associatebox_keyword" value="<?php echo $valuekeyword; ?>"></div>
<?php
$indexarray2 = array(
   "All" => esc_html__( 'All','associatebox-for-amazon' ),
    "Books" => esc_html__( 'Books','associatebox-for-amazon' ),
  "DVD" => esc_html__( 'DVD','associatebox-for-amazon' ),
  "Music" => esc_html__( 'Music','associatebox-for-amazon' ),
"VideoGames" => esc_html__( 'VideoGames','associatebox-for-amazon' ),
"Electronics" => esc_html__( 'Electronics','associatebox-for-amazon' ),
"Automotive" => esc_html__( 'Automotive','associatebox-for-amazon' ),
"Baby" => esc_html__( 'Baby','associatebox-for-amazon' ),
"Tools" => esc_html__( 'Tools','associatebox-for-amazon' ),
"PCHardware" => esc_html__( 'PcHardware','associatebox-for-amazon' ),
"HealthPersonalCare" => esc_html__( 'HealthPersonalCare','associatebox-for-amazon' ),
"Kitchen" => esc_html__( 'Kitchen','associatebox-for-amazon' ),
"Software" => esc_html__( 'Software','associatebox-for-amazon' ),
"Toys" => esc_html__( 'Toys','associatebox-for-amazon' ),
"Beauty" => esc_html__( 'Beauty','associatebox-for-amazon' ),
"MP3Downloads" => esc_html__( 'MP3Downloads','associatebox-for-amazon' ),
"MusicalInstruments" => esc_html__( 'MusicalInstruments','associatebox-for-amazon' ),
"Classical" => esc_html__( 'Classical','associatebox-for-amazon' ),
"Grocery" => esc_html__( 'Grocery','associatebox-for-amazon' ),
"Watches" => esc_html__( 'Watches','associatebox-for-amazon' ),
"Magazines" => esc_html__( 'Magazines','associatebox-for-amazon' ),
"Shoes" => esc_html__( 'Shoes','associatebox-for-amazon' ),
"HomeGarden" => esc_html__( 'HomeGarden','associatebox-for-amazon' ),
"GiftCards" => esc_html__( 'GiftCards','associatebox-for-amazon' ),
"PetSupplies" => esc_html__( 'PetSupplies','associatebox-for-amazon' ),
"Photo" => esc_html__( 'Photo','associatebox-for-amazon' ),
"Appliances" => esc_html__( 'Appliances','associatebox-for-amazon' ),
"Apparel" => esc_html__( 'Apparel','associatebox-for-amazon' ),
"Lighting" => esc_html__( 'Lighting','associatebox-for-amazon' ),
"ForeignBooks" => esc_html__( 'ForeignBooks','associatebox-for-amazon' ),
"Pantry" => esc_html__( 'Pantry','associatebox-for-amazon' ),
"MobileApps" => esc_html__( 'MobileApps','associatebox-for-amazon' ),
"OfficeProducts" => esc_html__( 'OfficeProducts','associatebox-for-amazon' ),
"KindleStore" => esc_html__( 'KindleStore','associatebox-for-amazon' ),
"Luggage" => esc_html__( 'Luggage','associatebox-for-amazon' ),
"Jewelry" => esc_html__( 'Jewelry','associatebox-for-amazon' ),
"Industrial" => esc_html__( 'Industrial','associatebox-for-amazon' ),


);

$searchindex2=get_post_meta($post->ID, "associatebox_mysearchindex2", true);

echo _e( 'SearchIndex: ', 'associatebox-for-amazon' );

echo '<select class="associateboxsearchindex2" name="associatebox_searchindex2">';

foreach ($indexarray2 as $a => $b) {


if ($a==$searchindex2) {
$selected4='selected="selected"';
} else {
$selected4="";
}
   echo "<option ".$selected4." value=".$a.">".$b."</option>";
}
echo "</select>";
echo '<div>';
echo _e( '<b>Amount Articles shown:</b>', 'associatebox-for-amazon' );

$associatebox_multiamount3=get_post_meta( $post->ID, 'associatebox_multiamount3', true );

//echo "AMOUNT3 ".$associatebox_multiamount3;
if(empty($associatebox_multiamount3)) {

//echo "LEER";
$associatebox_multiamount3=1;
}
echo'<select class="associatebox_amount" name="associatebox_multiamount3">';



for ($a=1;$a<=10;$a++) {
if ($a==$associatebox_multiamount3) {

$selected3='selected="selected"';
} else {

$selected3="";
}
echo "<option ".$selected3." value=".$a.">".$a."</option>";
}

echo "</select></div>";


?>
</div>
<?php
$art=get_post_meta($post->ID, "associatebox_art", true);

if ($art=="similarity") {

$checked4="checked";
}

?>
<div class="abstandmodi"></div>

<div class="boxsim"><?php echo _e( '<b>Search for Similarity:</b>', 'associatebox-for-amazon' ); ?>
<input id="similarity" class="associatebox_art" type="radio" name="associatebox_art" <?php echo $checked4; ?> value="similarity" >

<div style="clear:both;"></div>
<?php
$asinsim=get_post_meta($post->ID, "associatebox_asinsim", true);
?>
<div><label for="Similar Articles to'"><?php _e( 'Similar Articles to ASIN','associatebox-for-amazon' ); ?></label>&nbsp;

    <input type="text" name="associatebox_asinsim" id="associatebox_asinsim" value="<?php echo $asinsim; ?>"></div>
<?php 
	echo '<div>';
echo _e( '<b>Amount Articles shown:</b>', 'associatebox-for-amazon' );
echo'<select class="associatebox_amount" name="associatebox_multiamount4">';

$associatebox_multiamount4=get_post_meta( $post->ID, 'associatebox_multiamount4', true );

if(empty($associatebox_multiamount4)) {

$associatebox_multiamount4=1;
}
for ($g=1;$g<=10;$g++) {
if ($g==$associatebox_multiamount4) {

$selected3='selected="selected"';
} else {

$selected3="";
}
echo "<option ".$selected3." value=".$g.">".$g."</option>";
}
echo "</select></div>";
?>

</div>



<?php
}


add_action( 'admin_notices', '_location_admin_notices' );

function _location_admin_notices() {
global $post;
    $id = $post->ID;
  // If there are no errors, then we'll exit the function
  if ( ! ( $errors = get_transient( 'settings_errors' ) ) ) {
    return;
  }
  // Otherwise, build the list of errors that exist in the settings errores
  $message = '<div id="acme-message" class="error below-h2"><p><ul>';
  foreach ( $errors as $error ) {
    $message .= '<li>' . $error['message'] . '</li>';
  }
  $message .= '</ul></p></div><!-- #error -->';
  // Write them out to the screen
  echo $message;
  // Clear and the transient and unhook any other notices so we don't see duplicate messages
  delete_transient( 'settings_errors' );
remove_action( 'admin_notices', '_location_admin_notices' );

}

function associatebox_save_custom_meta_box($post_id, $post, $update)
{
global $post;

if(isset($_POST["associatebox_title"]))
    {
        $meta_box_text_value = sanitize_text_field($_POST["associatebox_title"]);
update_post_meta($post_id, "associatebox_title", esc_html($_POST["associatebox_title"]));
    } 
$id=$post->ID;
$art=$_POST['associatebox_art'];
$asins=$_POST['associatebox_asin'];

$browsenode=$_POST['associatebox_browsenode'];
$thekeyword=$_POST['associatebox_keyword'];
$asinsim=$_POST['associatebox_asinsim'];

if ($art=="asin" && empty($asins)) {
$message = __( "You forgot to set an ASIN with 'Search for ASINs'. Mode was set to 'No Amazon-Articles' therefore.", "associatebox-for-amazon" );
 add_settings_error(
    'MissingASIN.',
    'MissingASIN.',
    $message,
    'error'
  );
    
  set_transient( 'settings_errors', get_settings_errors(), 30 );
 
  
 update_post_meta($id, "associatebox_art", "empty");
return $post_id;
} else if ($art=="browse" && empty($browsenode)) {
$message = __( "You forgot to set a BrowseId with 'Items in BrowseNode'. Mode was set to 'No Amazon-Articles' therefore.", "associatebox-for-amazon" );
 add_settings_error(
    'MissingASIN.',
    'MissingASIN.',
    $message,
    'error'
  );
    
  set_transient( 'settings_errors', get_settings_errors(), 30 );
 
  
 update_post_meta($id, "associatebox_art", "empty");
return $post_id;

} else if ($art=="keyword" && empty($thekeyword)) {

$message = __( "You forgot to set a Keyword with 'Search for Keyword'. Mode was set to 'No Amazon-Articles' therefore.", "associatebox-for-amazon" );
 add_settings_error(
    'MissingASIN.',
    'MissingASIN.',
    $message,
    'error'
  );
    
  set_transient( 'settings_errors', get_settings_errors(), 30 );
 
  
 update_post_meta($id, "associatebox_art", "empty");
return $post_id;
} else if ($art=="similarity" && empty($asinsim)) {

$message = __( "You forgot to set an ASIN with 'Search for Similarity'. Mode was set to 'No Amazon-Articles' therefore.", "associatebox-for-amazon" );
 add_settings_error(
    'MissingASIN.',
    'MissingASIN.',
    $message,
    'error'
  );
    
  set_transient( 'settings_errors', get_settings_errors(), 30 );
 
  
 update_post_meta($id, "associatebox_art", "empty");
return $post_id;

}
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;

    if(!current_user_can("edit_post", $post_id))
        return $post_id;

    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;


if(isset($_POST["associatebox_placement"]))
    {
        $placement_box_text_value = sanitize_text_field($_POST["associatebox_placement"]);
update_post_meta($post_id, "associatebox_placement", esc_html($placement_box_text_value));
    }  

if(isset($_POST["associatebox_title"]))
    {
        $meta_box_text_value = sanitize_text_field($_POST["associatebox_title"]);
update_post_meta($post_id, "associatebox_title", esc_html($meta_box_text_value));
    }   
	if(isset($_POST["associatebox_shuffle"]))
    {
	        $shuffle = sanitize_text_field($_POST["associatebox_shuffle"]);
update_post_meta($post_id, "associatebox_shuffle", $shuffle);
    } 
	
	if(isset($_POST["associatebox_host"]))
    {
        $host_text_value = sanitize_text_field($_POST["associatebox_host"]);
update_post_meta($post_id, "associatebox_host", $host_text_value);
    }   


if($_POST["associatebox_art"]=="empty")


{

//DMS
$art_value = sanitize_text_field($_POST["associatebox_art"]);
update_post_meta($post_id, "associatebox_art", $art_value);
}
else if ($_POST["associatebox_art"]=="asin")
{
update_post_meta($post_id, "associatebox_art", "asin");
$asin_value = sanitize_text_field($_POST["associatebox_asin"]);
update_post_meta($post_id, "associatebox_asin", esc_html(esc_textarea($asin_value)));
if(isset($_POST["associatebox_multiamount"]))
    {
        $amount_value = sanitize_text_field($_POST["associatebox_multiamount"]);
update_post_meta($post_id, "associatebox_multiamount", $amount_value);
    } 
}

else if ($_POST["associatebox_art"]=="browse")
{
update_post_meta($post_id, "associatebox_art", "browse");
if(isset($_POST["associatebox_browsenode"]))
    {
        $browsenode= sanitize_text_field($_POST["associatebox_browsenode"]);
update_post_meta($post_id, "associatebox_browsenode", esc_html($browsenode));
    } 

if(isset($_POST["associatebox_searchindex"]))
    {
        $mysearchindex = sanitize_text_field($_POST["associatebox_searchindex"]);
update_post_meta($post_id, "associatebox_mysearchindex", $mysearchindex);
    }

if(isset($_POST["associatebox_multiamount2"]))
    {
        $amount_value2 = sanitize_text_field($_POST["associatebox_multiamount2"]);
update_post_meta($post_id, "associatebox_multiamount2", $amount_value2);
    } 

} else if ($_POST["associatebox_art"]=="keyword") {
update_post_meta($post_id, "associatebox_art", "keyword");
if(isset($_POST["associatebox_keyword"]))
    {
        $thekeyword= sanitize_text_field($_POST["associatebox_keyword"]);
update_post_meta($post_id, "associatebox_keyword", esc_html($thekeyword));
    } 
	
	if(isset($_POST["associatebox_searchindex2"]))
    {
        $mysearchindex2 = sanitize_text_field($_POST["associatebox_searchindex2"]);
update_post_meta($post_id, "associatebox_mysearchindex2", $mysearchindex2);

if(isset($_POST["associatebox_multiamount3"]))
    {
        $amount_value3 = sanitize_text_field($_POST["associatebox_multiamount3"]);
update_post_meta($post_id, "associatebox_multiamount3", $amount_value3);
    } 
    }
	
} else if ($_POST["associatebox_art"]=="similarity") {
update_post_meta($post_id, "associatebox_art", "similarity");

if(isset($_POST["associatebox_asinsim"]))
    {
        $asinsim = sanitize_text_field($_POST["associatebox_asinsim"]);
update_post_meta($post_id, "associatebox_asinsim", esc_html($asinsim));
    } 
	
	if(isset($_POST["associatebox_multiamount4"]))
    {
        $amount_value4 = sanitize_text_field($_POST["associatebox_multiamount4"]);
update_post_meta($post_id, "associatebox_multiamount4", $amount_value4);
    } 
}

}



function associatebox_page_create() {
    $page_title = 'associatebox';
    $menu_title = 'AssociateBox';
    $capability = 'edit_posts';
    $menu_slug = 'associatebox';
    $function = 'associatebox_page_display';
    $icon_url = '';
    $position = 24;

    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );

}



function associatebox_page_display() {

echo "<H3>"."AssociateBox"."</h3>";




 if (isset($_POST['deletecache'])) {

$dir = plugin_dir_path( __FILE__ )."cache/";

$handle=opendir($dir); 
while($data=readdir($handle)) 
    {
if(!is_dir($data) && $data!="." && $data!="..") {
unlink($dir.$data);
}
}
echo _e( '<b>Data deleted.</b>', 'associatebox-for-amazon' );
}

 if (isset($_POST['associatebox_standardhost'])) {
        
        $standardhost = sanitize_text_field($_POST['associatebox_standardhost']);

update_option('associatebox_standardhost', $standardhost);
    } 

$standardhost= get_option('standardhost', esc_html__( 'Standardhost','associatebox-for-amazon' ));

    if (isset($_POST['associatebox_access_key'])) {

$accesskey = sanitize_text_field($_POST['associatebox_access_key']);

$accesskey = esc_html($accesskey);
        update_option('associatebox_access_key', $accesskey);
        
    } 

    $accesskey = get_option('associatebox_access_key', esc_html__( 'Amazon Access Key','associatebox-for-amazon' ));


  if (isset($_POST['associatebox_sec_access_key'])) {
        
        $secret_key = sanitize_text_field($_POST['associatebox_sec_access_key']);
$secret_key = esc_html($secret_key);
update_option('associatebox_sec_access_key', $secret_key);
    } 

    $secret_key = get_option('associatebox_sec_access_key', esc_html__( 'Secret Amazon Access Key','associatebox-for-amazon' ));

  if (isset($_POST['associatebox_partner_id'])) {
$partnerid=sanitize_text_field($_POST['associatebox_partner_id']);
$partnerid = esc_html($partnerid);

        update_option('associatebox_partner_id', $partnerid);
        
    } 


$partnerid = get_option('associatebox_partner_id', esc_html__( 'Partner-ID','Your Partner ID' ));

	 if (isset($_POST['associatebox_partner_iduk'])) {
       $partneriduk=sanitize_text_field($_POST['associatebox_partner_iduk']);
$partneriduk=esc_html($partneriduk);
        update_option('associatebox_partner_iduk', $partneriduk);

        
    } 
    $partneriduk = get_option('associatebox_partner_iduk', esc_html__( 'Partner-ID UK','associatebox-for-amazon' ));
	
	 if (isset($_POST['associatebox_partner_idus'])) {
       $partneridus=sanitize_text_field($_POST['associatebox_partner_idus']);
$partneridus=esc_html($partneridus);

        update_option('associatebox_partner_idus', $partneridus);
        
    } 
    $partneridus = get_option('associatebox_partner_idus', esc_html__( 'Partner-ID US','associatebox-for-amazon' ));

		 if (isset($_POST['associatebox_partner_idfr'])) {
       $partneridfr=sanitize_text_field($_POST['associatebox_partner_idfr']);
$partneridfr=esc_html($partneridfr);
        update_option('associatebox_partner_idfr', $partneridfr);

        
    } 
    $partneridfr = get_option('associatebox_partner_idfr', esc_html__( 'Partner-ID FR','associatebox-for-amazon' ));

		 if (isset($_POST['associatebox_partner_ides'])) {
       $partnerides=sanitize_text_field($_POST['associatebox_partner_ides']);
$partnerides=esc_html($partnerides);
        update_option('associatebox_partner_ides', $partnerides);

        
    } 
$partnerides = get_option('associatebox_partner_ides', esc_html__( 'Partner-ID ES','associatebox-for-amazon' ));

?>
<form method="POST">
<?php
echo "<div>";
$standardhost=get_option('associatebox_standardhost');

//Standard-Locale

if (empty($standardhost)) {
$standardhost="de";
}

echo _e( 'Amazon Standard-Server:', 'associatebox-for-amazon' );
$hostarraystandard = array(
"de"=>"amazon.de (DE)",
"com"=>"amazon.com (US)",
"co.uk"=>"amazon.co.uk (GB)",
"fr"=>"amazon.fr (FR)",
"es"=>"amazon.es (ES)",

);
echo'<select class="associatebox_standardhost" name="associatebox_standardhost" >';
foreach ($hostarraystandard as $key2 => $wert2) {
if ($key2==$standardhost) {
$selectedhost2='selected="selected"';
} else {
$selectedhost2="";
}
echo "<option ".$selectedhost2." value=".$key2.">".$wert2."</option>";
}
echo "</select>";
echo "</div>";
?>
    <div><label for="Access Key"><?php _e( 'Amazon Access Key','associatebox-for-amazon' ); ?></label>
    <input type="text" name="associatebox_access_key" id="associatebox_access_key" value="<?php echo $accesskey; ?>"></div>

<div><label for="Secret Access Key"><?php _e( 'Secret Amazon Access Key','associatebox-for-amazon' ); ?></label>
    <input type="text" name="associatebox_sec_access_key" id="associatebox_sec_access_key" value="<?php echo $secret_key; ?>"></div>
<div><label for="Partner ID"><?php _e( 'Partner ID DE','associatebox-for-amazon' ); ?></label>
    <input type="text" name="associatebox_partner_id" id="associatebox_partner_id" value="<?php echo $partnerid; ?>"></div>
	
	<div><label for="Partner ID US"><?php _e( 'Partner ID US','associatebox-for-amazon' ); ?></label>
    <input type="text" name="associatebox_partner_idus" id="associatebox_partner_idus" value="<?php echo $partneridus; ?>"></div>
	<div><label for="Partner ID UK"><?php _e( 'Partner ID UK','associatebox-for-amazon' ); ?></label>
    <input type="text" name="associatebox_partner_iduk" id="associatebox_partner_iduk" value="<?php echo $partneriduk; ?>"></div>
	<div><label for="Partner ID FR"><?php _e( 'Partner ID FR','associatebox-for-amazon' ); ?></label>
    <input type="text" name="associatebox_partner_idfr" id="associatebox_partner_idfr" value="<?php echo $partneridfr; ?>"></div>
<div><label for="Partner ID ES"><?php _e( 'Partner ID ES','associatebox-for-amazon' ); ?></label>
    <input type="text" name="associatebox_partner_ides" id="associatebox_partner_ides" value="<?php echo $partnerides; ?>"></div>

	<div><input type="submit" value="<?php _e( 'Save','associatebox-for-amazon' ); ?>" class="button button-primary button-large"></div>
</form>
<div class="abstandeinstellungen"><div>
<form method="POST"><input type="submit" value="<?php _e( 'Delete Cache','associatebox-for-amazon' ); ?>" class="button button-primary button-large">
<input type="hidden" name="deletecache" id="deletecache" value="1">
</form>
<div class="deletemessage">
<?php
$cachedir = plugin_dir_path( __FILE__ )."cache/";
$files = scandir($cachedir);
$num_files = count($files)-2;
if ($num_files>0) {
$size=foldersize($cachedir);
$sizeumgerechnet=human_filesize($size);
}
if ($num_files==0) {
echo __("No files in cache.",'associatebox-for-amazon');
} else if($num_files==1) {
echo $num_files.__(" File in Cache ","associatebox-for-amazon")."(".$sizeumgerechnet.").";
} else {
echo $num_files.__(" Files in Cache ","associatebox-for-amazon")."(".$sizeumgerechnet.").";


}
?>
</div>
</div>
</div>

<?php

}
function foldersize($path) {
    $total_size = 0;
    $files = scandir($path);
    $cleanPath = rtrim($path, '/'). '/';

    foreach($files as $t) {
        if ($t<>"." && $t<>"..") {
            $currentFile = $cleanPath . $t;
            if (is_dir($currentFile)) {
                $size = foldersize($currentFile);
                $total_size += $size;
            }
            else {
                $size = filesize($currentFile);
                $total_size += $size;
            }
        }   
    }

    return $total_size;
}


function human_filesize($bytes, $decimals = 2) {
    $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

// Widget ausschalten, wenn keine ASINS vorhanden
function associatebox_my_disable_widget( $sidebars_widgets ) {


global $wp_query;
if( is_object( $wp_query ) ) {

$page_id=$wp_query->get_queried_object_id();

} else {




global $post;
$page_id=$post->ID;
}


$asins=get_post_meta( $page_id, 'associatebox_asin', true );
$art=get_post_meta($page_id, "associatebox_art", true);
$browseid=get_post_meta($page_id, "associatebox_art", true);
$keyword=get_post_meta($page_id, "associatebox_keyword", true);

$placement=get_post_meta($page_id, "associatebox_placement", true);


if ($sidebars_widgets) {
    foreach( $sidebars_widgets as $widget_area => $widget_list ){
foreach( $widget_list as $pos => $widget_id ){



$pos2 = strpos($widget_id, "associatebox");
if($pos2===false) {
} else  {



if ($placement=="under") {


unset( $sidebars_widgets[$widget_area][$pos] );

} else {
if (!$art) {
unset( $sidebars_widgets[$widget_area][$pos] );
} else {
if ($art=="empty") {
unset( $sidebars_widgets[$widget_area][$pos] );
}
if ($art=="asin") {
if(empty($asins) OR !$asins) {
unset( $sidebars_widgets[$widget_area][$pos] );
}
} else if ($art=="browse") {
if(empty($browseid) OR !$browseid) {
unset( $sidebars_widgets[$widget_area][$pos] );
}
} else if ($art=="keyword") {

if(empty($keyword) OR !$keyword) {
unset( $sidebars_widgets[$widget_area][$pos] );
}
}
}
}

}
}
}
}
return $sidebars_widgets;
}












function aws_query($extraparams,$path,$host='de',$arrayback) {



    $private_key = get_option('associatebox_sec_access_key');
	


		if ($host=="de") {
$partnerid=get_option('associatebox_partner_id');
}
else if ($host=="co.uk") {
$partnerid=get_option('associatebox_partner_iduk');
} 
else if ($host=="com") {
$partnerid=get_option('associatebox_partner_idus');
} else if ($host=="fr") {
$partnerid=get_option('associatebox_partner_idfr');
} else if ($host=="es") {
$partnerid=get_option('associatebox_partner_ides');;
}


//echo $host."<BR>";
    $method = "GET";
    $host = "webservices.amazon.".$host;
    $uri = "/onca/xml";

	

 $params = array(
        "AssociateTag" => $partnerid,
        "Service" => "AWSECommerceService",
        "AWSAccessKeyId" => get_option('associatebox_access_key'),
        "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
        "SignatureMethod" => "HmacSHA256",
        "SignatureVersion" => "2",
        "Version" => "2013-08-01",

    );
	

//echo "PARTNER_ID ".$params["AssociateTag"];

    foreach ($extraparams as $param => $value) {
        $params[$param] = $value;
    }

    ksort($params);

    // sort the parameters
    // create the canonicalized query
    $canonicalized_query = array();
    foreach ($params as $param => $value) {
        $param = str_replace("%7E", "~", rawurlencode($param));
        $value = str_replace("%7E", "~", rawurlencode($value));
        $canonicalized_query[] = $param . "=" . $value;
    }
    $canonicalized_query = implode("&", $canonicalized_query);


    // create the string to sign
    $string_to_sign =
        $method . "\n" .
        $host . "\n" .
        $uri . "\n" .
        $canonicalized_query;

    // calculate HMAC with SHA256 and base64-encoding
    
	 $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $private_key, TRUE));


    // encode the signature for the request
     $signature = str_replace('%7E', '~', rawurlencode($signature));

    // Put the signature into the parameters
    $params["Signature"] = $signature;
    uksort($params, "strnatcasecmp");

    // TODO: the timestamp colons get urlencoded by http_build_query
    //       and then need to be urldecoded to keep AWS happy. Spaces
    //       get reencoded as %20, as the + encoding doesn't work with 
    //       AWS
    $query = urldecode(http_build_query($params));
    $query = str_replace(' ', '%20', $query);

    $string_to_send = "https://" . $host . $uri . "?" . $query;

//echo "<div><a target=\"_blank\" href=\"".$string_to_send."\">XML</a></div>";

$dir = plugin_dir_path( __FILE__ );
if ($params['Operation']=="ItemLookup" && $params['ItemId']) {
$cachedatei = $dir."cache/".$params['Operation']."_"."ASINSearch"."_".str_replace(",","-",$params['ItemId'])."_".$host.".xml";
}

else if ($params['Operation']=="ItemSearch" && $params[ 'SearchIndex'] && $params['BrowseNode']) {
$cachedatei = $dir."cache/"."BrowseNode_".$params['Operation']."_".$params['SearchIndex']."_".str_replace(",","-",$params['BrowseNode'])."_".$host.".xml";
} else if ($params['Keywords']) {


$cachedatei=$dir."cache/"."Keywords_".$params['Operation']."_".$params['Keywords']."_".$params['SearchIndex']."_".$host.".xml";
} else if ($params['Operation']=="SimilarityLookup") {
$cachedatei=$dir."cache/".$params['Operation']."_".$params['ItemId']."_".$host.".xml";
}
$cachezeit = time()-3600;




if (file_exists($cachedatei)){

if (filemtime($cachedatei)<$cachezeit){

 $daten = @file_get_contents($string_to_send);
	                  @file_put_contents($cachedatei, $daten);
 } else {

$daten = @file_get_contents($cachedatei);

}
} else {
$daten = @file_get_contents($string_to_send);



@file_put_contents($cachedatei, $daten);
}


$response = simplexml_load_string($daten);  
if ($response) {
if ($arrayback=="yes") {
$response2 = json_decode(json_encode((array) $response), 1);
$response = array($response->getName() => $response2);

} 
}
return $response;




}

function associatebox_append($content) {

global $wp_query;
$page_object = get_queried_object();
$page_id     = get_queried_object_id();


$placement=get_post_meta($page_id, "associatebox_placement", true);
if ($placement=="under") {

$art=get_post_meta($page_id, "associatebox_art", true);
if ($art=="empty") {
return;
}
$host=get_post_meta($page_id, "associatebox_host", true);
if (empty($host)) {
$host="de";
}


if ($host=="de") {
$partnerid=get_option('associatebox_partner_id');
}
else if ($host=="co.uk") {
$partnerid=get_option('associatebox_partner_iduk');
} 
else if ($host=="com") {
$partnerid=get_option('associatebox_partner_idus');
} else if ($host=="fr") {
$partnerid=get_option('associatebox_partner_idfr');
} else if ($host=="es") {
$partnerid=get_option('associatebox_partner_ides');;
}




wp_enqueue_style( 'associatebox', plugins_url()."/associatebox-for-amazon/css/associatebox.css" );

$custom_title=get_post_meta($page_id, "associatebox_title", true); 
$output.='<H5 class="associatebox_title">'.$custom_title.'</H5>';
$output.='<div class="itemsundertext">';
//-----------------------------------------------------------------------------------------------------------------

if ($art=="keyword") {

$multi3=get_post_meta( $page_id, 'associatebox_multiamount3', true );
$keyword=get_post_meta($page_id, "associatebox_keyword", true);
$searchindex2=get_post_meta($page_id, "associatebox_mysearchindex2", true);

$params=array(

        "Operation" => "ItemSearch",
        "Keywords" => $keyword,
        "SearchIndex" => $searchindex2,
      

  "ResponseGroup" => "Medium" 
    );

$response=aws_query($params,ABSPATH,$host,"yes");

//var_dump($response);
if (!$response) {
$output.= '<div class="errorapi1">'.__('<b>ERROR</b>','associatebox-for-amazon').'</div>';
$output.= '<div class="errorapi2">'.__('Please check your keys for the Amazon Product API.','associatebox-for-amazon').'</div>';
return;
}
if ($response[ItemSearchResponse][Items][Request][Errors]) { //FEHLER?
$output.= '<div class="centerdata"><b>'.$response[ItemSearchResponse][Items][Request][Errors][Error][Code].'</b></div>';
$output.= '<div class="centerdata2">'.$response[ItemSearchResponse][Items][Request][Errors][Error][Message].'</div>';
} //FEHLER
else {
$anzahl=count($response[ItemSearchResponse][Items][Item]);

$multi3=get_post_meta( $page_id, 'associatebox_multiamount3', true );
if ($anzahl<$multi3) { //MULTI 3
$multi3=$anzahl;
}
$shuffle=get_post_meta($page_id, "associatebox_shuffle", true);
//$output.= "SHUFFLE" .$shuffle;
if ($shuffle=="shuffle") {
shuffle($response[ItemSearchResponse][Items][Item]);
}
for($count = 0; $count < $multi3; $count++)
{
$item=$response[ItemSearchResponse][Items][Item][$count];

if ($count==$multi3-1) {
$last="last";
} else {
$last="";
}



$width=100/$multi3;
$item=$response[ItemSearchResponse][Items][Item][$count];
$output.= '<div style="width:'.$width.'%;" class="centerdata item'.$last.'">';
$output.= '<div class="itemheader"><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'">'.htmlspecialchars($item[ItemAttributes][Title]).'</a></div>';
$output.= '<div class="abstandhalter"></div>';
$output.= '<div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'"><img src="'.$item[MediumImage][URL].'" border="0" alt="Cover"></a></div>';
$output.= "</div>";


}

}

} else if ($art=="browse") { //BROWSE


$browseid=get_post_meta($page_id, "associatebox_browsenode", true);
$searchindex=get_post_meta($page_id, "associatebox_mysearchindex", true);

$params=array(

        "Operation" => "ItemSearch",
        "BrowseNode" => $browseid,
        "SearchIndex" => $searchindex,
        "ResponseGroup" => "Medium" 
    );

$response=aws_query($params,ABSPATH,$host,"yes");
//var_dump($response);
if (!$response) {
$output.= '<div class="errorapi1">'.__('<b>ERROR</b>','associatebox-for-amazon').'</div>';
$output.= '<div class="errorapi2">'.__('Please check your keys for the Amazon Product API.','associatebox-for-amazon').'</div>';
return;
}
$multi2=get_post_meta( $page_id, 'associatebox_multiamount2', true );

//var_dump($response[ItemSearchResponse][Items][Request][Errors]);
if ($response[ItemSearchResponse][Items][Request][Errors]) { //FEHLER?

$output.= '<div class="centerdata"><b>'.$response[ItemSearchResponse][Items][Request][Errors][Error][Code].'</b></div>';
$output.= '<div class="centerdata2">'.$response[ItemSearchResponse][Items][Request][Errors][Error][Message].'</div>';

} else {

$anzahl=count($response[ItemSearchResponse][Items][Item]);

$multi2=get_post_meta( $page_id, 'associatebox_multiamount2', true );
if ($anzahl<$multi2) { //MULTI 2
$multi2=$anzahl;
}
$shuffle=get_post_meta($page_id, "associatebox_shuffle", true);

if ($shuffle=="shuffle") {
shuffle($response[ItemSearchResponse][Items][Item]);
}
for($count = 0; $count < $multi2; $count++)
{

if ($count==$multi2-1) {
$last="last";
} else {
$last="";
}
$width=100/$multi2;
$item=$response[ItemSearchResponse][Items][Item][$count];
$output.= '<div style="width:'.$width.'%;" class="centerdata item'.$last.'">';
$output.= '<div class="itemheader"><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'">'.htmlspecialchars($item[ItemAttributes][Title]).'</a></div>';
$output.= '<div class="abstandhalter"></div>';
$output.= '<div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'"><img src="'.$item[MediumImage][URL].'" border="0" alt="Cover"></a></div>';
$output.= "</div>";


}


} //FEHLER ODER NICHT? ENDE BROWSE

} else if ($art=="similarity") { //SIM

$asinsim=get_post_meta($page_id, "associatebox_asinsim", true);

$params = array(
  'Operation' => 'SimilarityLookup',
  'ItemId' => $asinsim,
  
  'ResponseGroup' => 'Medium'
  );
$response=aws_query($params,ABSPATH,$host,"yes");

if (!$response) {
$output.= '<div class="errorapi1">'.__('<b>ERROR</b>','associatebox-for-amazon').'</div>';
$output.= '<div class="errorapi2">'.__('Please check your keys for the Amazon Product API.','associatebox-for-amazon').'</div>';
return;
}
//var_dump($response[SimilarityLookupResponse]);
if ($response[SimilarityLookupResponse][Items][Request][Errors]) {

$output.= '<div class="centerdata"><b>'.$response[SimilarityLookupResponse][Items][Request][Errors][Error][Code].'</b></div>';
$output.= '<div class="centerdata2">'.$response[SimilarityLookupResponse][Items][Request][Errors][Error][Message].'</div>';

} else { //ANZAHL
$anzahl=count($response[SimilarityLookupResponse][Items][Item]);
//$output.= "ANZAHL  ".$anzahl;

$multi4=get_post_meta( $page_id, 'associatebox_multiamount4', true );
if ($anzahl<$multi4) { //MULTI 4
$multi4=$anzahl;
}
$shuffle=get_post_meta($page_id, "associatebox_shuffle", true);
if ($shuffle=="shuffle") {
shuffle($response[SimilarityLookupResponse][Items][Item]);
}
for($count = 0; $count < $multi4; $count++)
{
$item=$response[SimilarityLookupResponse][Items][Item][$count];

if ($count==$multi4-1) {
$last="last";
} else {
$last="";
}
$width=100/$multi4;
$output.= '<div style="width:'.$width.'%;" class="centerdata item'.$last.'">';
$output.= '<div class=itemheader><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'">'.htmlspecialchars($item[ItemAttributes][Title]).'</a></div>';
$output.= '<div class="abstandhalter"></div>';
$output.= '<div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'"><img src="'.$item[MediumImage][URL].'" border="0" alt="Cover"></a></div>';
$output.= "</div>";


}
} //ANZAHL
} //SIM
elseif ($art=="asin") { //ASINS

$asins=get_post_meta( $page_id, 'associatebox_asin', true );

//echo $asins;
$asins=explode(",",$asins);
$shuffle=get_post_meta($page_id, "associatebox_shuffle", true);
//echo $shuffle;
if ($shuffle=="shuffle") {
shuffle($asins);
}
//echo $asins;
$multi=get_post_meta( $page_id, 'associatebox_multiamount', true );


if ($multi>sizeof($asins)) {
$multi=sizeof($asins);
}

$asins = array_slice($asins, 0, $multi); 

$asins=implode(",",$asins);
$params=array(
  'Operation' => 'ItemLookup',
  'ItemId' => $asins,
  'ResponseGroup' => 'Medium'
  );
$response=aws_query($params,ABSPATH,$host,"no");

if (!$response) {
$output.= '<div class="errorapi1">'.__('<b>ERROR</b>','associatebox-for-amazon').'</div>';
$output.= '<div class="errorapi2">'.__('Please check your keys for the Amazon Product API.','associatebox-for-amazon').'</div>';
return;
}

if ($response->Items->Request->Errors->Error) {
$output.= '<div style="text-align:center;"><b>'.$response->Items->Request->Errors->Error->Code.'</b></div>';
$output.= '<div style="text-align:center;margin-top:10px;">'.$response->Items->Request->Errors->Error->Message.'</div>';
} else {

$max=count($response->Items->Item);
//$output.= $max."<BR>";
$counter=1;


$output.='<div style="width:100%;">';

foreach ($response->Items->Item as $item) {
//$output.= $counter;

if ($counter==$max) {
$last="last";
} else {
$last="";
}
$width=100/$max;


$output.='<div style="display:inline-block;vertical-align:bottom;width:'.$width.'% !important;" class="centerdata item'.$last.'">';
$output.='<div class="itemheader"><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item->ASIN.'?tag='.$partnerid.'">'.htmlspecialchars($item->ItemAttributes->Title).'</a></div>';
$output.= '<div class="centerdata2"><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item->ASIN.'?tag='.$partnerid.'"><img src="'.$item->MediumImage->URL.'" border="0" alt="Cover"></a></div>';

$output.='</div>';
/*
$output.= '<div style="display:inline-block;background-color:red;width:'.$width.'% !important;" class="centerdata item'.$last.'"><div class="itemheader"><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item->ASIN.'?tag='.$partnerid.'">'.htmlspecialchars($item->ItemAttributes->Title).'</a></div>';
$output.= '<div class="abstandhalter"></div>';
$output.= '<div class="centerdata2"><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item->ASIN.'?tag='.$partnerid.'"><img src="'.$item->MediumImage->URL.'" border="0" alt="Cover"></a></div>';
*/
$counter++;

}

$output.="</div>";
}

}

//-----------------------------------------------------------------------------------------------------------------
$output.="</div>";
$content=$content.$output;
}
return $content;
}
?>
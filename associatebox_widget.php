<?php

 
class Associatebox_Widget extends WP_Widget {



// Widget kreiern
public function __construct() {
		$widget_ops = array( 
			'classname' => 'Associatebox Widget',
			'description' => 'Product Api Widget for Amazon',
		);
		parent::__construct( 'Associatebox_Widget', 'Associatebox Widget', $widget_ops );
	}

 
  function form($instance)
  {
    $instance = wp_parse_args((array) $instance, array( 'title' => '' ));
    $title = $instance['title'];
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  function widget($args, $instance)
  {

wp_enqueue_style( 'associatebox', plugins_url()."/associatebox-for-amazon/css/associatebox.css" );

    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
 

global $wp_query;
$page_object = get_queried_object();
$page_id     = get_queried_object_id();
//echo "ID: ".$page_id;

$custom_title=get_post_meta($page_id, "associatebox_title", true); 

if ( ! empty( $custom_title ) ) {
$title=$custom_title;
}
    if (!empty($title)) {
      echo $before_title . $title . $after_title;
}

 
    // Do Your Widgety Stuff Here...
    
$art=get_post_meta($page_id, "associatebox_art", true);

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
$partnerid=get_option('associatebox_partner_ides');
}

//echo "HOST:".$host;
//echo "ART ".$art;
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
echo '<div class="errorapi1">'.__('<b>ERROR</b>','associatebox-for-amazon').'</div>';
echo '<div class="errorapi2">'.__('Please check your keys for the Amazon Product API.','associatebox-for-amazon').'</div>';
return;
}
if ($response[ItemSearchResponse][Items][Request][Errors]) { //FEHLER?
echo '<div class="centerdata"><b>'.$response[ItemSearchResponse][Items][Request][Errors][Error][Code].'</b></div>';
echo '<div class="centerdata2">'.$response[ItemSearchResponse][Items][Request][Errors][Error][Message].'</div>';
} //FEHLER
else {
$anzahl=count($response[ItemSearchResponse][Items][Item]);

$multi3=get_post_meta( $page_id, 'associatebox_multiamount3', true );
if ($anzahl<$multi3) { //MULTI 3
$multi3=$anzahl;
}
$shuffle=get_post_meta($page_id, "associatebox_shuffle", true);
//echo "SHUFFLE" .$shuffle;
if ($shuffle=="shuffle") {
shuffle($response[ItemSearchResponse][Items][Item]);
}
for($count = 0; $count < $multi3; $count++)
{
$item=$response[ItemSearchResponse][Items][Item][$count];
echo '<div class="centerdata">';
echo '<div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'">'.htmlspecialchars($item[ItemAttributes][Title]).'</a></div>';
echo '<div class="abstandhalter"></div>';
echo '<div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'"><img src="'.$item[MediumImage][URL].'" border="0" alt="Cover"></a></div>';
echo "</div>";

if ($count<$multi3) {
echo '<div class="grosserabstand"></div>';
}
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
echo '<div class="errorapi1">'.__('<b>ERROR</b>','associatebox-for-amazon').'</div>';
echo '<div class="errorapi2">'.__('Please check your keys for the Amazon Product API.','associatebox-for-amazon').'</div>';
return;
}
$multi2=get_post_meta( $page_id, 'associatebox_multiamount2', true );

//var_dump($response[ItemSearchResponse][Items][Request][Errors]);
if ($response[ItemSearchResponse][Items][Request][Errors]) { //FEHLER?

echo '<div class="centerdata"><b>'.$response[ItemSearchResponse][Items][Request][Errors][Error][Code].'</b></div>';
echo '<div class="centerdata2">'.$response[ItemSearchResponse][Items][Request][Errors][Error][Message].'</div>';

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
$item=$response[ItemSearchResponse][Items][Item][$count];
echo '<div class="centerdata">';
echo '<div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'">'.htmlspecialchars($item[ItemAttributes][Title]).'</a></div>';
echo '<div class="abstandhalter"></div>';
echo '<div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'"><img src="'.$item[MediumImage][URL].'" border="0" alt="Cover"></a></div>';
echo "</div>";

if ($count<$multi2) {
echo '<div class="grosserabstand"></div>';
}
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
echo '<div class="errorapi1">'.__('<b>ERROR</b>','associatebox-for-amazon').'</div>';
echo '<div class="errorapi2">'.__('Please check your keys for the Amazon Product API.','associatebox-for-amazon').'</div>';
return;
}
//var_dump($response[SimilarityLookupResponse]);
if ($response[SimilarityLookupResponse][Items][Request][Errors]) {

echo '<div class="centerdata"><b>'.$response[SimilarityLookupResponse][Items][Request][Errors][Error][Code].'</b></div>';
echo '<div class="centerdata2">'.$response[SimilarityLookupResponse][Items][Request][Errors][Error][Message].'</div>';

} else { //ANZAHL
$anzahl=count($response[SimilarityLookupResponse][Items][Item]);
//echo "ANZAHL  ".$anzahl;

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
echo '<div class="centerdata">';
echo '<div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'">'.htmlspecialchars($item[ItemAttributes][Title]).'</a></div>';
echo '<div class="abstandhalter"></div>';
echo '<div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item[ASIN].'?tag='.$partnerid.'"><img src="'.$item[MediumImage][URL].'" border="0" alt="Cover"></a></div>';
echo "</div>";

if ($count<$multi4) {
echo '<div class="grosserabstand"></div>';
}
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
echo '<div class="errorapi1">'.__('<b>ERROR</b>','associatebox-for-amazon').'</div>';
echo '<div class="errorapi2">'.__('Please check your keys for the Amazon Product API.','associatebox-for-amazon').'</div>';
return;
}

if ($response->Items->Request->Errors->Error) {
echo '<div style="text-align:center;"><b>'.$response->Items->Request->Errors->Error->Code.'</b></div>';
echo '<div style="text-align:center;margin-top:10px;">'.$response->Items->Request->Errors->Error->Message.'</div>';
} else {

$max=count($response->Items->Item);
//echo $max."<BR>";
$counter=1;
foreach ($response->Items->Item as $item) {
//echo $counter;
echo '<div class="centerdata"><div><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item->ASIN.'?tag='.$partnerid.'">'.htmlspecialchars($item->ItemAttributes->Title).'</a></div>';
echo '<div class="abstandhalter"></div>';
echo '<div class="centerdata2"><a target="_blank" href="http://www.amazon.'.$host.'/gp/product/'.$item->ASIN.'?tag='.$partnerid.'"><img src="'.$item->MediumImage->URL.'" border="0" alt="Cover"></a></div></div>';
if ($counter<$max) {
echo '<div class="grosserabstand"></div>';
}
$counter++;
}
}

} //ASINS



 echo $after_widget;
 



}
}
 
?>
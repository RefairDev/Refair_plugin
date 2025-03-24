<?php
/**
 * The admin-partials view of the plugin.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 */

namespace Refairplugin\Metas\Views;

use Refairplugin\Refairplugin_Meta_View;

/**
 * Class managing Locmap meta view display and saving.
 */
class Refairplugin_LocMap_Meta_View extends Refairplugin_Meta_View {

	/**
	 * Slug identifiing type of the view meta.
	 *
	 * @var string
	 */
	public static $type = 'locmap';

	/**
	 * Constructor of the class Set internal variables.
	 *
	 * @param  array $options Options used to initialize meta view.
	 */
	public function __construct(
		$options = array()
	) {
		parent::__construct( $options );
	}

	/**
	 * Function used to add inline script tag.
	 *
	 * @param  array $data Data to use to generate metabox content.
	 *
	 * @return void
	 */
	/**
	 * Function used to add inline script tag.
	 *
	 * @param  string $apikey API key for google maps api requests.
	 * @param  string $name Name of the meta.
	 * @param  string $uniqid Unique identifier of the preview map.
	 * @return void
	 */
	protected function script( $apikey, $name, $uniqid ) {
		?>
		<style>
			#<?php echo esc_attr( $name ); ?>-<?php echo esc_attr( $uniqid ); ?>-map{
				height: 350px;
				width: 400px;
			}
			p>label + input{
			margin-left:7px;
			}
			.ui-dialog.location-result{
				z-index:900;
			}
		</style>
		<script type="text/javascript">

			var moduleLOCMAP_<?php echo esc_attr( $uniqid ); ?> = (function($){

				var $lat = $('[name="<?php echo esc_attr( $name ); ?>[lat]"]');
				var $lng = $('[name="<?php echo esc_attr( $name ); ?>[lng]"]');
				var $location = $('[name="<?php echo esc_attr( $name ); ?>[location]"]');
				var locationResults=[];
				var resultsDialog={};
				var map={};
				var mapDefaultCenter = [44.8368, -0.5896];
				var mapDefaultZoom = 11;
				var locMarker={};

				this.selectLocationResult = jQuery.proxy(this.selectLocationResult,this);

				return {

					init: function(){
						jQuery('#search-<?php echo esc_attr( $uniqid ); ?>').on('click', moduleLOCMAP_<?php echo esc_attr( $uniqid ); ?>.manageSearchClick)
						
						resultsDialog = jQuery(".<?php echo esc_attr( $name ); ?>-location-results-dialog").dialog({
							autoOpen : false,
							title: <?php echo wp_kses( __( 'Choisissez votre résultat', 'refair-plugin' ), wp_kses_allowed_html( 'post' ) ); ?>,
							dialogClass: "no-close location-result",
							buttons: [{
								text: "Valider",
								click: function() {
									moduleLOCMAP_<?php echo esc_attr( $uniqid ); ?>.selectLocationResult();
									$( this ).dialog( "close" );
									}
								}],
						});

						let initCenter=[0,0];
						if ($lat.val() != "" && $lng.val() !=""){
							let locLat = parseFloat($lat.val());
							let locLng = parseFloat($lng.val());
							initCenter=[locLat,locLng];
						}else{
							initCenter = mapDefaultCenter;
						}


						map = L.map("<?php echo esc_attr( $name ); ?>-<?php echo esc_attr( $uniqid ); ?>-map", {center:initCenter, zoom:mapDefaultZoom  });
						L.tileLayer('https://a.tile.openstreetmap.org/{z}/{x}/{y}.png', {
							attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
						}).addTo(map);

						if ( $lat.val() != "" && $lng.val() !=""){
							this.locMarker = L.marker([$lat.val(),$lng.val()]).addTo(map);
						}
						map.on("click",this.manageClickEvent,this);

					},

					manageSearchClick: function(e){
						
						e.preventDefault();

						var value = $(this).prev().val();
						
						jQuery.ajax({
							url: "https://maps.googleapis.com/maps/api/geocode/json?address="+value+"&language=fr&region=fr&key=<?php echo esc_attr( $apikey ); ?>",
							success: function(resp) {
								if(! resultsDialog.dialog( "isOpen" )){
									switch(resp.status)
									{
										case "OK":
										{
											locationResults = resp.results;
		
											locationResults.forEach(function(valeurCourante,index ,resultArray){
												jQuery(".<?php echo esc_attr( $name ); ?>-result-list").append('<li class="location-result"><input type="radio" name="<?php echo esc_attr( $name ); ?>-location-choice" value="'+ index +'">'+ valeurCourante.formatted_address +'</li>');	
											});
											resultsDialog.dialog( "open" );
											break;
										}
										case"ZERO_RESULTS":
										{
											alert("No result returned, correct and renew your request"); 
											break;
										}
										default:
										{
											alert('Error: Google geocode returned "'+ resp.status +'"' );
											
										}
									}
								}
							}
						});
					},

					selectLocationResult: function(){
						var resultValue = jQuery("[name='<?php echo esc_attr( $name ); ?>-location-choice']").val();

						this.placeMarker(locationResults[resultValue].geometry.location.lat,locationResults[resultValue].geometry.location.lng);

						if(jQuery("[name='keep-label-<?php echo esc_attr( $uniqid ); ?>']").prop("checked")!==true){
							$location.val(locationResults[resultValue].formatted_address);
						}
						
						jQuery(".<?php echo esc_attr( $name ); ?>-result-list").empty();
						resultsDialog.dialog("close");
					},

					manageClickEvent: function(e){
						this.placeMarker(e.latlng.lat,e.latlng.lng);
					},
					placeMarker: function(lat,lng){

						if (this.locMarker instanceof L.Marker){
							this.locMarker.removeFrom(map);
						}
						this.locMarker = L.marker([lat,lng]).addTo(map);
						$lat.val(lat);
						$lng.val(lng);
					}
					
					

				} 			
					

			})(jQuery);


			window.onload=()=>{
				moduleLOCMAP_<?php echo esc_attr( $uniqid ); ?>.init();
			};

			
			/*jQuery.ajax({
				url: 'https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=' + API_KEY,
				success: function(resp) {
					console.log(resp)
				}
			})*/
		</script>
		<?php
	}

	/**
	 * Get html content of the meta box.
	 *
	 * @param  string $view_content Previous content of the view of post meta boxes.
	 * @param  array  $data Data to use to generate metabox content.
	 * @param  mixed  $value Value to set to metabox inputs.
	 * @return string Content of view added with the current metabox.
	 */
	public function get_view( $view_content, $data, $value = null ) {

		$uniqid   = uniqid();
		$location = '';
		$lat      = '';
		$lng      = '';

		if ( ! empty( $value ) && is_array( $value ) ) {
			if ( array_key_exists( 'location', $value ) ) {
				$location = $value['location'];}
			if ( array_key_exists( 'lat', $value ) ) {
				$lat = $value['lat'];}
			if ( array_key_exists( 'lng', $value ) ) {
				$lng = $value['lng'];}
		}

		ob_start();
		?>
		<p>
			<label for="<?php echo esc_attr( $data['name'] ); ?>[location]">Adresse</label><input type="text" name="<?php echo esc_attr( $data['name'] ); ?>[location]" id="<?php echo esc_attr( $data['name'] ); ?>[location]" class="meta-video regular-text" value="<?php echo wp_kses_post( $location ); ?>"/>
			<button id="search-<?php echo esc_attr( $uniqid ); ?>">Search</button>
		</p>
		<p>
			<label for="<?php echo esc_attr( $data['name'] ); ?>[lat]">Latitude</label><input type="text" name="<?php echo esc_attr( $data['name'] ); ?>[lat]" id="<?php echo esc_attr( $data['name'] ); ?>[lat]" class="meta-video regular-text" value="<?php echo wp_kses_post( $lat ); ?>"/>
			<label for="<?php echo esc_attr( $data['name'] ); ?>[lng]">Longitude</label><input type="text" name="<?php echo esc_attr( $data['name'] ); ?>[lng]" id="<?php echo esc_attr( $data['name'] ); ?>[lng]" class="meta-video regular-text" value="<?php echo wp_kses_post( $lng ); ?>"/>
		</p>
		<div class="clearfix">
			<div id="<?php echo esc_attr( $data['name'] ); ?>-<?php echo esc_attr( $uniqid ); ?>-map">
			
			</div>
		</div>
		<div class="<?php echo esc_attr( $data['name'] ); ?>-location-results-dialog">
		<ul class="<?php echo esc_attr( $data['name'] ); ?>-result-list">   		
		</ul>
		<input id="keep-label-<?php echo esc_attr( $uniqid ); ?>" type="checkbox" name="keep-label-<?php echo esc_attr( $uniqid ); ?>" value="keep_label"><label for="keep-label-<?php echo esc_attr( $uniqid ); ?>">Garder la description</label>
		</div>
		<?php

		$this->script( get_option( 'google_api_key','[GOOGLE_API_KEY]' ), $data['name'], $uniqid );
		return $view_content . ob_get_clean();
	}
}

<?php

class LRInterfaceFeatured extends WP_Widget
{
  function LRInterfaceFeatured()
  {
    $widget_ops = array('classname' => 'LRInterfaceFeatured', 'description' => 'Display a preview of featured resources' );
    $this->WP_Widget('LRInterfaceFeatured', 'LR Interface Featured Resources', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'resources' => '', 'total' => '' ) );
    $title = $instance['title'];
    $resources = $instance['resources'];
    $total = $instance['total'];
?>

<p>

	<label for="<?php echo $this->get_field_id('title'); ?>">
		Title: 
	</label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
	<br/><br/>		
	
	<label for="<?php echo $this->get_field_id('total'); ?>">
		Enter desired number of featured resource boxes: 
	</label>
	
	
	<select class="widefat" id="<?php echo $this->get_field_id('total'); ?>" name="<?php echo $this->get_field_name('total'); ?>" type="text">
	
	 <?php
		
		for($g = 1; $g < 6; $g++):
	 ?>
	 
			<option value="<?php echo $g; ?>" <?php echo $total == $g ? 'selected="selected"' : ''; ?>><?php echo $g; ?></option>
	 <?php
	 
		endfor;
	 ?>
	
	</select>
	
	
	<br/><br/>	
	
	<label for="<?php echo $this->get_field_id('resources'); ?>">
		Resource ID pool separated by semicolons: 
	</label>
	<textarea class="widefat" rows="10" id="<?php echo $this->get_field_id('resources'); ?>" name="<?php echo $this->get_field_name('resources'); ?>" type="text"><?php echo attribute_escape($resources); ?></textarea>
	<br/><br/>
</p>
  
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = trim($new_instance['title']);
    $instance['resources'] = trim($new_instance['resources']);
    $instance['total'] = $new_instance['total'];
    return $instance;
  }
  
   function display_rand_resource($arr, $host, $title, $total, $args)
  {
	echo $total;
	if($total > sizeof($arr) || (sizeof($arr) == 1 && trim($arr[0]) == trim($_GET['lr_resource'])))
		return false;
	
	extract($args, EXTR_SKIP);
	echo $before_widget;
	echo $before_title . $title . $after_title;
	
	$g = 0;
	$save_arr = array();

	while($g < $total){
	
		$temp = rand(0, sizeof($arr) - 1);
		
		
		echo $temp;
		if(in_array(trim($arr[$temp]), $save_arr)){
		
			echo ': in! ';
		}
		
		else echo ': not! ';
		
		if(trim($arr[$temp]) == trim($_GET['lr_resource']) || in_array(trim($arr[$temp]), $save_arr)){
		
			continue;
		}
		
		else{
			
			$save_arr[$g] = trim($arr[$temp]);
			$g++;
		}
	}
	
	?>
	<script type="text/javascript">
		var serviceHost = "<?php echo $host; ?>";
		var NODE_URL = '<?php echo empty($options['node'])?"http://node01.public.learningregistry.net/":$options['node']; ?>';

		var qmarkUrl = '<?php echo plugins_url( "/images/qmark.png" , dirname(__FILE__) ) ?>';
		
		<?php if(empty($_GET['query'])){
			include_once('templates/scripts/applicationPreview.php'); 
		} ?>
		
		$(document).ready(function(){
			$.getJSON(serviceHost + '/data/?keys=' + encodeURIComponent('<?php echo json_encode($save_arr); ?>'),function(data){		
			
				return; 
				var md5 = '<?php echo trim($arr[$i]); ?>';
				
				if(data[0]){
					data = data[0];
					var src = data.url;
					
					//This is done because observable.valueHasMutated wasn't working..
					var currentObject = new resourceObject("Item", src);
					currentObject.timeline = self.currentObject().timeline;
					currentObject.title = (data.title == undefined) ? doTransform(src) : data.title;
					currentObject.description = (data.description == undefined) ? "" : data.description;
					currentObject.url = (data.url == undefined) ? "" : data.url;
					
					console.log("qmarkUrl: ", qmarkUrl);
					var imageUrl = qmarkUrl? qmarkUrl:"/images/qmark.png";
					
					currentObject.image = (data.hasScreenshot !== true) ? imageUrl : serviceHost + "/screenshot/" + md5;
					currentObject.image = self.getImageSrc(data.url, currentObject.image);
					currentObject.hasScreenshot = currentObject.image != imageUrl;				
					
					self.featuredResource.push(currentObject);
				}
				
				else{
					
					src = data.url;
					var imageUrl = qmarkUrl? qmarkUrl:"/images/qmark.png";
					var currentObject = new resourceObject("Item", src);
					currentObject.image = self.getImageSrc(data.url, imageUrl);
					currentObject.hasScreenshot = currentObject.image != imageUrl;		
					
					self.featuredResource.push(currentObject);
				}
			});
		});
	</script>
	<div style="margin-bottom: 10px;">
		<a data-bind="text:getShorterStr(featuredResource().title, 45), attr:{href:wordpressLinkTransform(permalink,featuredResource().url), title:featuredResource().title}" class="title"></a><br/>
	</div>
	<a data-bind="attr:{href:wordpressLinkTransform(permalink,featuredResource().url)}" class="title">
		<img data-bind="attr:{src:$root.getImageSrc(false, serviceHost + '/screenshot/<?php echo trim($arr[$i]); ?>')}" class="img-polaroid" />
	</a>

	<?php
	
	return true;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
	$options = get_option('lr_options_object');
	
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
    $resources = empty($instance['resources']) ? array('') : explode(';', $instance['resources']);
    $host  = empty($options['host']) ? "http://12.109.40.31" : $options['host'];
    $total  = empty($instance['total']) ? 1 : $instance['total'];

    if (!empty($title) && $this->display_rand_resource($resources, $host, $title, $total, $args) != false){
	
	  echo $after_widget;
	}
  }
}
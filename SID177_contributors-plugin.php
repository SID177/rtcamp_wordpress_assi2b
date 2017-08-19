<?php
/*

358
1
2

*	Plugin Name: SID177 Contributors Plugin
*	Author: SID177
*	Description: You can add co-authors to any post and can choose to show them
*	on your post on front-end.
*	Version: 1.0.0
*/

class SID177_contributors_plugin{
    public function __construct(){
        // add_shortcode('SID177_contributors_shortcode',array($this,'SID177_contributors_shortcode_html'));

    	/*
		*	ADD FILTERS HERE
    	*/
    	add_filter('manage_'.$this->post_type.'s_columns',array($this,'SID177_coauthor_posttable_head'));
    	add_filter('the_content',array($this,'SID177_contributors_filtercontent'));
        add_filter('user_has_cap',array($this,'SID177_contributors_changecap'),10,3);


    	/*
		*	ADD ACTIONS HERE
    	*/
        add_action('add_meta_boxes',array($this,'SID177_contributors_addmetabox'));
        add_action('save_post',array($this,'SID177_contributors_addauthor'),5,3);
        add_action('manage_'.$this->post_type.'s_custom_column',array($this,'SID177_coauthor_posttable'),10,2);
        add_action('delete_post',array($this,'SID177_contributors_clearauthor'),10);
        add_action('pre_get_posts',array($this,'SID177_contributors_modifyquery1'));
        add_action('the_posts',array($this,'SID177_contributors_modifyquery'));

        
        $this->css=plugins_url()."/".plugin_basename( __DIR__ )."/assets/css/";
        $this->js=plugins_url()."/".plugin_basename( __DIR__ )."/assets/js/";

        $this->frontend_style=$this->css."frontend-style.css";
        $this->frontend_script=$this->js."frontend-app.js";

        $this->admin_style=$this->css."admin-style.css";
        $this->admin_script=$this->js."admin-app.js";
    }
    
    private $coauthor_metakey="SID177_coauthor";
    private $coauthor_showmultiple="SID177_showmultiple";
    private $coauthor_shortcode="SID177_contributors_shortcode";
    private $post_type="post";
    private $metabox_id="SID177_contributors_metabox";
    private $metabox_title="Add Co-Authors";
    private $metabox_context="side";
    private $metabox_priority="high";

    private $css,$js;
    private $frontend_style,$frontend_script;
    private $admin_style,$admin_script;

    private $role_name="SID177_contributor";
    private $role_display="SID177 Post Contributor";

    /*public function SID177_contributors_shortcode_html($attr=[],$content=null){
        $attr = array_change_key_case((array)$attr, CASE_LOWER);
        $values = shortcode_atts([
            'id'=>'0'
        ], $attr,'');
        $values['id']=trim($values['id']);
        if($values['id']=='0' || $values['id']=='')
            return "";

        $authors=explode(",",get_post_meta($values['id'],$this->coauthor_metakey)[0]);
        $users = new WP_User_Query( array( 'include' => $authors ) );
        $users=$users->results;
        ob_start();
        if(isset($users[0]))
            echo "<hr/><strong>Co-Authors: </strong><br/>";
        foreach ($users as $user) {
            echo "<div style='display:inline-block; padding:10px 20px 10px 0px;'>";
            echo "<a href='http://localhost/wordpress/author/".$user->user_nicename."/'>";
            echo get_avatar($user->ID)."<br/>";
            echo $user->user_login."</a></div>";
        }
        echo "<hr/>";
        $o=ob_get_contents();
        ob_end_clean();
        return $o;
    }*/

    public function SID177_contributors_filtercontent($content=null){
    	wp_enqueue_style('admin-style.css',$this->admin_style);
    	wp_enqueue_script('admin-col.js',$this->js."admin-col.js");
    	global $post;

    	$show_multiple=get_post_meta($post->ID,$this->coauthor_showmultiple);
    	if(count($show_multiple)==0)
    		return $content;

    	$authors=get_post_meta($post->ID,$this->coauthor_metakey);
    	if(count($authors)==0)
    		return $content;

        $users = new WP_User_Query( array( 'include' => $authors ) );
        $users=$users->results;
        ob_start();
        if(isset($users[0])){
        	echo "<br>";
        	?>
        	<div class="container" style="border: solid 1px #717171; border-radius: 4px !important;">
        		<button class="accordion">Contributors</button>
        		<div class="panel">
				  	<?php
			      	foreach ($users as $user) {
			      		?>
				        <div style='display:inline-block; padding:10px 20px 10px 0px;'>
				        	<center>
				      		<a style="text-align: center;" href="<?php echo esc_url(get_author_posts_url($user->ID)); ?>">
				      			<div class='avatar_wrapper'>
				      			<?php echo get_avatar($user->ID); ?>
				      			</div>
				      			<?php echo esc_html($user->user_login); ?>
				      		</a>
				      		</center>
				        </div>
				        <?php
				    }
				    ?>
				</div>
			</div>
        	<?php
        }
        $o=ob_get_contents();
        ob_end_clean();
        return $content.$o;
    }

    // public function SID177_cont

    public function SID177_contributors_metabox(){
        wp_enqueue_script('admin-app.js',$this->admin_script);
        ?>
        <style type="text/css">
            #<?php echo esc_html($this->metabox_id); ?> .co-authors{
                max-height:10em;
                overflow: auto;
            }
        </style>
        <div>
            <input style="width: 100%;" type="text" name="" onkeyup="changeSearch(this.value)" placeholder="Search by NAME / EMAIL / USERNAME">
            <!-- <br/>
            <strong>Show: </strong>
            <input type="checkbox" onchange="changed()" id="SID177_coauthors_selected_checkbox" name="SID177_coauthors_show">Selected
            &nbsp;
            <input type="checkbox" onchange="changed()" id="SID177_coauthors_not_checkbox" name="SID177_coauthors_show">Not-Selected -->
        </div>
        <br/>
        <div class="co-authors">
            <?php
            global $post;
            $authors=get_post_meta($post->ID,$this->coauthor_metakey);
            
            $temp_authors=array();
            for ($i=0; $i < count($authors); $i++) { 
                $temp_authors[$authors[$i]]=1;
            }
            $authors=$temp_authors;
            
            $users=get_users();
            foreach ($users as $user) {
                if($post->post_author==$user->ID)
                    continue;
                if(!user_can($user->ID,"edit_posts"))
                	continue;
                $firstname=get_user_meta($user->ID,'first_name',true);
                $lastname=get_user_meta($user->ID,'last_name',true);
                ?>
                <div id="<?php echo esc_html($user->user_login.' '.$firstname.' '.$lastname.' '.$user->display_name.' '.$user->user_email); ?>">
                	<input type="checkbox" value="<?php echo esc_html($user->ID); ?>" form="post" name="author[]" <?php echo esc_html(isset($authors[$user->ID])?"checked":""); ?> />
                	<?php echo esc_html((empty($firstname) || empty($lastname)?$user->display_name:$firstname.' '.$lastname)." (".$user->user_login.")"); ?>
                	<br/>
                </div>
                <?php
            }
            $show_multiple=get_post_meta($post->ID,$this->coauthor_showmultiple);
            $show_multiple=count($show_multiple)==0?null:$show_multiple;
            ?>
        </div>
        <br/>
        <input type="hidden" form="post" name="author_update" value="author_update" />
        <div>
            
            <input type="checkbox" form="post" name="show_multiple" value="show_multiple" <?php echo esc_html(is_null($show_multiple)?"":"checked"); ?> />
            <!-- <br/> -->
            <!-- <strong>Paste this shortcode to show co-authors in the post.</strong> -->
            <strong>Show multiple aurhors in this post.</strong>
        </div>
        <?php
    }

    public function SID177_contributors_addmetabox(){
        $posts=get_post_types();
        foreach($posts as $post) {
            add_meta_box($this->metabox_id,$this->metabox_title,array($this,'SID177_contributors_metabox'),$post,$this->metabox_context,$this->metabox_priority);
        }
    }

    public function SID177_contributors_addauthor($post_id,$post,$update){
        if(isset($_REQUEST['author_update'])){
            $authors=isset($_REQUEST['author'])?$_REQUEST['author']:"";
            $new_authors=array();
            // $authors=sanitize_text_field($_REQUEST['author']);

            delete_post_meta($post_id,$this->coauthor_metakey);
            if(is_array($authors)){
            	foreach ($authors as $author) {
	            	$user=get_userdata($author);
	            	if($user){
	            		add_post_meta($post_id,$this->coauthor_metakey,$author);
	            	}
	            }
            }

            if(isset($_REQUEST['show_multiple'])){
	        	update_post_meta($post_id,$this->coauthor_showmultiple,sanitize_text_field($_REQUEST['show_multiple']));
	        }else{
	        	delete_post_meta($post_id,$this->coauthor_showmultiple);
	        }
        }
    }

    public function SID177_contributors_clearauthor($post_id){
    	// delete_post_meta($post_id,$this->coauthor_metakey);
    	// delete_post_meta($post_id,$this->coauthor_showmultiple);
    }

    public function SID177_coauthor_posttable_head($defaults) {
        $defaults['co-author']='Co-Authors';
        return $defaults;
    }
    public function SID177_coauthor_posttable($column_name,$post_id) {
        global $post;
        if($column_name=='co-author') {
            $authors=get_post_meta($post->ID,$this->coauthor_metakey);
            if(!isset($authors[0]))
            	return;
            $users=new WP_User_Query(array('include'=>$authors));
            $users=$users->results;
            foreach($users as $user)
                echo esc_html($user->user_login.", ");
        }
    }


    public function SID177_contributors_changecap($allcaps,$caps,$args){
        $post_id=isset($args[2])?$args[2]:0;
        $obj=get_post_type_object(get_post_type($post_id));
        if(!$obj)
            return $allcaps;
        $authors=get_post_meta($post_id,$this->coauthor_metakey);
        if(!$authors)
            return $allcaps;
        // $authors=explode(',',$authors);
        foreach ($authors as $author) {
            if($author==wp_get_current_user()->ID){
                $allcaps['edit_others_posts']=true;
                $allcaps['edit_published_posts']=true;
                $allcaps['edit_private_posts']=true;
                // $allcaps[$obj->cap->edit_published_posts]=true;
                // $allcaps[$obj->cap->edit_private_posts]=true;
                $allcaps['edit_posts']=true;
                // $allcaps[$obj->cap->edit_posts]=true;
                $allcaps['publish_posts']=true;
                return $allcaps;
            }
        }
        return $allcaps;
    }

    public function SID177_contributors_modifyquery1($qry){
    	if(empty($qry->query['author_name']) || $qry->is_admin())
    		return;
    	session_start();
    	$_SESSION['prepost']=$qry->query['author_name'];
    }

    public function SID177_contributors_modifyquery($posts){
    	if(!isset($_SESSION['prepost']))
    		return $posts;
    	$author=$_SESSION['prepost'];
    	unset($_SESSION['prepost']);

    	$author=get_user_by('slug',$author);
    	if(!$author)
    		return $posts;

    	global $wpdb;
    	$coposts=$wpdb->get_results("select post_id from wp_postmeta where meta_key='".$this->coauthor_metakey."' and meta_value='".$author->ID."'");
    	foreach ($coposts as $copost) {
    		$post=get_post($copost->post_id);
    		if($post && $post->post_status=='publish'){
    			$postauthor=get_userdata($post->post_author);
    			if(!$postauthor)
    				continue;
    			$content="<h3 style='color:red;'>This is a contribution post!<br>Created by: <strong><a href='".get_author_posts_url($postauthor->ID)."'>".$postauthor->user_login."</a></strong></h3>";
    			$post->post_content=$content."".$post->post_content;
    			array_push($posts,$post);
    		}
    	}
    	// echo "contr<br/>";
    	foreach ($posts as $post) {
    		// echo $post->post_title."<br>";
    	}
    	// die;
    	return $posts;
    }
}
new SID177_contributors_plugin();
?>
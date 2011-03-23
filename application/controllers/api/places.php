<?php defined('BASEPATH') OR exit('No direct script access allowed');
/* 
 * Locations API : Core : Social-Igniter
 *
 */
class Places extends Oauth_Controller
{
    function __construct() 
    {
        parent::__construct();
                
    	$this->form_validation->set_error_delimiters('', '');
    }

    function index_get()
    {
        $locations = $this->locations_model->get_locations();
        
        if($locations)
        {
            $message = array('status' => 'success', 'data' => $locations);
        }
        else
        {
            $message = array('status' => 'error', 'message' => 'Could not find any locations');
        }
        
        $this->response($message, 200);
    }
    
 	function create_authd_post()
	{
		// Validation Rules
	   	$this->form_validation->set_rules('address', 'Address', 'required');
	   	$this->form_validation->set_rules('title', 'Title', 'required');
	   	$this->form_validation->set_rules('content', 'Content', 'required');

		// Passes Validation
	    if ($this->form_validation->run() == true)
	    {
	    	if ($this->input->post('site_id')) $site_id = $this->input->post('site_id');
	    	else $site_id = config_item('site_id');
	    	
	    	$content_data = array(
	    		'site_id'			=> $site_id,
				'parent_id'			=> $this->input->post('parent_id'),
				'category_id'		=> $this->input->post('category_id'),
				'module'			=> 'places',
				'type'				=> 'place',
				'source'			=> $this->input->post('source'),
				'order'				=> 0,
	    		'user_id'			=> $this->oauth_user_id,
				'title'				=> $this->input->post('title'),
				'title_url'			=> form_title_url($this->input->post('title'), $this->input->post('title_url')),
				'content'			=> $this->input->post('content'),
				'details'			=> $this->input->post('details'),
				'access'			=> $this->input->post('access'),
				'comments_allow'	=> config_item('places_comments_allow'),
				'geo_lat'			=> $this->input->post('geo_lat'),
				'geo_long'			=> $this->input->post('geo_long'),
				'viewed'			=> 'Y',
				'approval'			=> 'Y',
				'status'			=> form_submit_publish($this->input->post('status'))  			
	    	);

			// Insert
			$result = $this->social_igniter->add_content($content_data);	    	

	    	if ($result)
		    {
		    	// Process Tags
				if ($this->input->post('tags')) $this->social_tools->process_tags($this->input->post('tags'), $result['content']->content_id);				
				
				// Add Place
				$place_data = array(
					'content_id'	=> $result['content']->content_id,
					'address'		=> $this->input->post('address'),
					'district'		=> $this->input->post('district'),
					'locality'		=> $this->input->post('locality'),
					'region'		=> $this->input->post('region'),
					'country'		=> $this->input->post('country'),
					'postal'		=> $this->input->post('postal')
				);
				
				$place = $this->social_tools->add_place($place_data);
				
	        	$message = array('status' => 'success', 'message' => 'Awesome we created your place', 'data' => $result['content'], 'activity' => $result['activity'], 'place' => $place);
	        }
	        else
	        {
		        $message = array('status' => 'error', 'message' => 'Oops we were unable to create your place');
	        }	
		}
		else 
		{
	        $message = array('status' => 'error', 'message' => validation_errors());
		}

	    $this->response($message, 200);
	}
         
    
    function modify_authd_post()
    {    
    	$content = $this->social_igniter->get_content($this->get('id'));
    
		// Access Rules
	   	//$this->social_tools->has_access_to_modify($this->input->post('type'), $this->get('id') $this->oauth_user_id);
	   	
    	$viewed			= 'Y';
    	$approval		= 'A'; 
   
    	$content_data = array(
    		'content_id'		=> $this->get('id'),
			'parent_id'			=> $this->input->post('parent_id'),
			'category_id'		=> $this->input->post('category_id'),
			'order'				=> $this->input->post('order'),
			'title'				=> $this->input->post('title'),
			'title_url'			=> form_title_url($this->input->post('title'), $this->input->post('title_url'), $content->title_url),
			'content'			=> $this->input->post('content'),
			'details'			=> $this->input->post('details'),
			'access'			=> $this->input->post('access'),
			'comments_allow'	=> $this->input->post('comments_allow'),
			'geo_lat'			=> $this->input->post('geo_lat'),
			'geo_long'			=> $this->input->post('geo_long'),
			'viewed'			=> $viewed,
			'approval'			=> $approval,
			'status'			=> form_submit_publish($this->input->post('status'))
    	);
    									
		// Insert
		$update = $this->social_igniter->update_content($content_data, $this->oauth_user_id); 
        
	    if ($update)
	    {
			// Process Tags    
			if ($this->input->post('tags')) $this->social_tools->process_tags($this->input->post('tags'), $content->content_id);
							
			// Add Place
			$place_data = array(
				'content_id'	=> $result['content']->content_id,
				'address'		=> $this->input->post('address'),
				'district'		=> $this->input->post('district'),
				'locality'		=> $this->input->post('locality'),
				'region'		=> $this->input->post('region'),
				'country'		=> $this->input->post('country'),
				'postal'		=> $this->input->post('postal')
			);
			
			$place = $this->social_tools->add_place($place_data);			
			
	    
        	$message = array('status' => 'success', 'message' => 'Awesome, we updated your '.$this->input->post('type'), 'data' => $update);
        }
        else
        {
	        $message = array('status' => 'error', 'message' => 'Oops, we were unable to post your '.$this->input->post('type'));
        }        
        
        $this->response($message, 200);
    }
    
    function destroy_delete()
    {
        $message 	= array('id' => $this->get('id'), 'message' => 'DELETED!');
        $response	= 200;
        
        $this->response($message, $response);
    }
    

}
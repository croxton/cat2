<?php

$plugin_info = array(
  'pi_name' => 'Cat2',
  'pi_version' =>'1.1.1',
  'pi_author' =>'Mark Croxton',
  'pi_author_url' => 'http://www.hallmark-design.co.uk/',
  'pi_description' => 'Convert between category name, category id and category url title',
  'pi_usage' => Cat2::usage()
  );

class Cat2 {
	
	public $return_data = '';
	public $category_url_title;
	public $category_id;
	public $category_name;
	public $category_group;
	public $site;
	private $_debug;
	
	/** 
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	function Cat2() 
	{
		$this->EE =& get_instance();	
		$this->site = $this->EE->config->item('site_id');
		
		// register parameters
		$this->category_url_title = strtolower($this->EE->TMPL->fetch_param('category_url_title', ''));
		$this->category_name = strtolower($this->EE->TMPL->fetch_param('category_name', ''));
		$this->category_id = preg_replace("/[^0-9]/", '', $this->EE->TMPL->fetch_param('category_id', NULL));
		$this->category_group = $this->EE->TMPL->fetch_param('category_group', '');
		$this->_debug = (bool) preg_match('/1|on|yes|y/i', $this->EE->TMPL->fetch_param('debug'));	
		
		// set up cache
		if ( ! isset($this->EE->session->cache[__CLASS__]))
        {
            $this->EE->session->cache[__CLASS__] = array();
        }
	}
	
	/** 
	 * exp:cat2:id
	 *
	 * @access public
	 * @return string
	 */
	function id() 
	{
		if (empty($this->category_url_title) && empty($this->category_name))
		{
			// parameter required, fail gracefully
			if ($this->_debug)
			{
				show_error(__CLASS__.' error: one of the following parameters is required: category_url_title OR category_name.');
			}
			return;
		}
		
		if (!empty($this->category_url_title))
		{
			$key 	= "cat_url_title";
			$value 	= $this->category_url_title;
		}
		else
		{
			$key 	= "cat_name";
			$value	 = $this->category_name;
		}
		
		return $this->cat_query('cat_id', $key, $value);
	}
	
	
		
	/** 
	 * exp:cat2:name
	 *
	 * @access public
	 * @return string
	 */
	function name() 
	{
		if (empty($this->category_url_title) && empty($this->category_id))
		{
			// parameter required, fail gracefully
			if ($this->_debug)
			{
				show_error(__CLASS__.' error: one of the following parameters is required: category_url_title OR category_id.');
			}
			return;
		}
		
		if ( ! empty($this->category_url_title))
		{
			$key 	= "cat_url_title";
			$value 	=  $this->category_url_title;
		}
		else
		{
			$key 	= "cat_id";
			$value	 = $this->category_id;
		}
		
		return $this->cat_query('cat_name', $key, $value);
	}
	
	/** 
	 * exp:cat2:url_title
	 *
	 * @access public
	 * @return string
	 */
	function url_title() 
	{
		if (empty($this->category_name) && empty($this->category_id))
		{
			// parameter required, fail gracefully
			if ($this->_debug)
			{
				show_error(__CLASS__.' error: one of the following parameters is required: category_name OR category_id.');
			}
			return;
		}
		
		if ( ! empty($this->category_name))
		{
			$key 	= "cat_name";
			$value 	=  $this->category_name;
		}
		else
		{
			$key 	= "cat_id";
			$value	 = $this->category_id;
		}
		
		return $this->cat_query('cat_url_title', $key, $value);
	}
	
	/** 
	 * The main query
	 *
	 * @access public
	 * @param string $col 	the column we want to get
	 * @param string $key  	the column we're searching
	 * @param string $value the column we're searching
	 * @return string
	 */
	protected function cat_query($col, $key, $value)
	{
		if ( ! isset($this->EE->session->cache[__CLASS__][$col][$value]) )
		{
			// query
			$this->EE->db->select($col);
			$this->EE->db->from('exp_categories');
			$this->EE->db->where('site_id', $this->site);
		
			if ($key == 'cat_id')
			{
				$this->EE->db->where($key, $value);
			}
			else
			{
				$this->EE->db->where("LOWER({$key})", $value);
			}
			
			if ( ! empty($this->category_group))
			{
				if (strpos($this->category_group, '|') !== false)
				{
					$this->EE->db->where_in('group_id', explode('|', $this->category_group));
				}
				else
				{
					$this->EE->db->where('group_id', $this->category_group);
				}
			}
			
			// run the query
			$results = $this->EE->db->get();
			
			if ($results->num_rows() > 0) 
			{
				$this->EE->session->cache[__CLASS__][$col][$value] = $results->row($col);
			}
			else
			{
				// fail gracefully
				$this->EE->session->cache[__CLASS__][$col][$value] = '';
				
				if ($this->_debug)
				{
					show_error(__CLASS__.' error: category not found.');
				}
			}
		}
		
		// is this a tag pair?
		$tagdata = $this->EE->TMPL->tagdata;
	
		if ( ! empty($tagdata))
		{
			return $this->EE->TMPL->swap_var_single(
							'category_id', 
							$this->EE->session->cache[__CLASS__][$col][$value], 
							$tagdata
					);
		}
		else
		{
			// output direct
			return $this->EE->session->cache[__CLASS__][$col][$value];
		}
	}

	// usage instructions
	public static function usage()
	{
  		ob_start();
?>
-------------------
HOW TO USE
-------------------

Convert between category name, category id and category url title.
Query results are cached, so you can use the same tag multiple times
in your template without additional overhead.

Tags:
{exp:cat2:id}
{exp:cat2:name}
{exp:cat2:url_title}

Required Parameters:
category_url_title=
category_name=
category_id=
One of these must be present in order to find the correct category.

Optional Parameters:
category_group=
Filter category results by a specific category group ID, or multiple category group IDs.
Examples:
category_group="2"
category_group="5|6"

debug=
Output error messages if tag is used incorrectly. Can be "yes" or "no" (default is "no").

Examples:

category_id from category_url_title:
{exp:cat2:id category_url_title="my_category"}

category_id from category_name:
{exp:cat2:id category_name="my category"}

category_name from category_id:
{exp:cat2:name category_id="25"}

category_name from category_url_title:
{exp:cat2:name category_url_title="my_category"}

category_url_title from category_id:
{exp:cat2:url_title category_id="25"}

category_url_title from category_name:
{exp:cat2:url_title category_name="my category"}

Can also be used as a tag pair, e.g.:

{exp:cat2:id category_url_title="my_category" parse="inward"}
	{category_id}
{/exp:cat2:id}

{exp:cat2:name category_id="25" parse="inward"}
	{category_name}
{/exp:cat2:name}

{exp:cat2:url_title category_id="25" parse="inward"}
	{category_url_title}
{/exp:cat2:url_title}


	<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}

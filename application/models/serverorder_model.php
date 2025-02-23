<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class serverorder_model extends CI_Model
{

	public function __construct()
	{
		parent:: __construct();
		$this->tbl_name = "gsm_server_orders";
		$this->tbl_services = "gsm_server_services";
		$this->tbl_apis = "gsm_apis";
		$this->tbl_members = "gsm_members";
	}
    
    public function count_where($params) 
    {
		$this->db->from($this->tbl_name)->where($params);
        return  $this->db->count_all_results();
	}
	
	public function get_count_by($col)
	{
		$query = $this->db
              ->select("{$col}, count({$col}) AS countof")
              ->group_by($col)
              ->get($this->tbl_name);
		return $query->result();
	}    
	
	public function get_where($params) 
	{
        $query = $this->db->get_where($this->tbl_name, $params);
        return $query->result_array();
    }    
    
	public function get_where_in(array $id) 
	{
		$this->db->select("{$this->tbl_name}.ID, {$this->tbl_name}.RequiredFields, {$this->tbl_services}.Title", TRUE);
		$this->db->from($this->tbl_name);
		$this->db->join($this->tbl_services, "{$this->tbl_name}.ServerServiceID={$this->tbl_services}.ID", "inner");
		
		$this->db->where_in("{$this->tbl_name}.ID", $id);
		$this->db->where("{$this->tbl_name}.Status", 'Pending');
        $query = $this->db->get();
        return $query->result_array();
	}
	
	public function get_pending_orders() 
	{
		$this->db->select("$this->tbl_apis.LibraryID, $this->tbl_apis.Host, $this->tbl_apis.Username, $this->tbl_apis.ApiKey")
		->select("$this->tbl_services.ToolID, $this->tbl_name.Quantity, $this->tbl_name.*")
		->from($this->tbl_apis)
		->join($this->tbl_services, "$this->tbl_apis.ID = $this->tbl_services.ApiID")
		->join($this->tbl_name, "$this->tbl_services.ID = $this->tbl_name.ServerServiceID")
		->where(array("$this->tbl_name.Status" => "Pending", "$this->tbl_name.ReferenceID" => NULL))
		->order_by("$this->tbl_name.ID", "ASC");
		
        $query = $this->db->get();
        return $query->result_array();
	}
	
	public function get_requested_pending_orders() 
	{
		$this->db->select("$this->tbl_apis.LibraryID, $this->tbl_apis.Host, $this->tbl_apis.Username, $this->tbl_apis.ApiKey")
		->select("$this->tbl_services.ToolID, $this->tbl_name.Email, $this->tbl_name.ID, $this->tbl_name.ReferenceID")
		->select("$this->tbl_members.Email AS MemberEmail, $this->tbl_members.FirstName, $this->tbl_members.LastName, $this->tbl_name.MemberID")
		->from($this->tbl_apis)
		->join($this->tbl_services, "$this->tbl_apis.ID = $this->tbl_services.ApiID")
		->join($this->tbl_name, "$this->tbl_services.ID = $this->tbl_name.ServerServiceID")
		->join($this->tbl_members, "$this->tbl_name.MemberID = $this->tbl_members.ID")
		->where("$this->tbl_name.Status", "Pending")
		->where("`$this->tbl_name`.`ReferenceID` IS NOT NULL", NULL, false)
		->order_by("$this->tbl_name.ID", "ASC");
		
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_all() 
    {                
        $query = $this->db->get($this->tbl_name);
        return $query->result_array();
    }
    
    public function count_all() 
    {
        $query = $this->db->count_all($this->tbl_name);
        return $query;
    }

    public function insert($data) 
    {
        $this->db->insert($this->tbl_name, $data);
        $id = $this->db->insert_id();
        return intval($id);
    }

    public function update($data, $id)
    {   
        return $this->db->update($this->tbl_name, $data, array('ID' => $id));
    }

    public function delete($id)
    {
        return $this->db->delete($this->tbl_name, array('ID' => $id));                
    }   
	
	public function get_server_data($id)
	{
		$this->load->library('odatatables');
		$this->odatatables				
				->select("{$this->tbl_name}.ID, {$this->tbl_services}.Title, {$this->tbl_name}.Code, {$this->tbl_name}.Email, {$this->tbl_name}.Notes, {$this->tbl_name}.Status, {$this->tbl_name}.CreatedDateTime", TRUE)
				->from($this->tbl_name)
				->join($this->tbl_services, "{$this->tbl_name}.ServerServiceID={$this->tbl_services}.ID", "inner")
				->where("{$this->tbl_name}.MemberID", $id);
					
		return $this->odatatables->generate();
	}	
	
	public function get_server_data_new($id, $start, $length, $cari_data)
	{
		if(!empty($cari_data)){
			$sql = "SELECT $this->tbl_name.ID, $this->tbl_services.Title, $this->tbl_services.Price, $this->tbl_name.Code, $this->tbl_name.Email, $this->tbl_name.Notes, $this->tbl_name.Status, $this->tbl_name.CreatedDateTime, $this->tbl_name.UpdatedDateTime FROM $this->tbl_name INNER JOIN $this->tbl_services ON $this->tbl_name.ServerServiceID = $this->tbl_services.ID WHERE $this->tbl_name.MemberID = 1 AND ( $this->tbl_services.Title LIKE '%".$cari_data."%' OR $this->tbl_name.Code LIKE '%".$cari_data."%' OR $this->tbl_name.Email LIKE '%".$cari_data."%' OR $this->tbl_name.Notes LIKE '%".$cari_data."%' OR $this->tbl_name.Status LIKE '%".$cari_data."%' ) ORDER BY $this->tbl_name.ID DESC LIMIT $start, $length";
		}else{
			$sql = "SELECT $this->tbl_name.ID, $this->tbl_services.Title, $this->tbl_services.Price, $this->tbl_name.Code, $this->tbl_name.Email, $this->tbl_name.Notes, $this->tbl_name.Status, $this->tbl_name.CreatedDateTime, $this->tbl_name.UpdatedDateTime FROM $this->tbl_name INNER JOIN $this->tbl_services ON $this->tbl_name.ServerServiceID = $this->tbl_services.ID WHERE $this->tbl_name.MemberID = 1 AND ( $this->tbl_services.Title LIKE '%%' OR $this->tbl_name.Code LIKE '%%' OR $this->tbl_name.Email LIKE '%%' OR $this->tbl_name.Notes LIKE '%%' OR $this->tbl_name.Status LIKE '%%' ) ORDER BY $this->tbl_name.ID DESC LIMIT $start, $length";
		}

		$result = $this->db->query($sql);
		return $result->result_array();
	}	

	public function get_file_data_select($id, $status)
	{
		$this->load->library('odatatables');
		$this->odatatables				
				->select("{$this->tbl_name}.ID, {$this->tbl_name}.IMEI, {$this->tbl_services}.Title, {$this->tbl_name}.Code, {$this->tbl_name}.Note, {$this->tbl_name}.Status, {$this->tbl_name}.CreatedDateTime", TRUE)
				->from($this->tbl_name)
				->join($this->tbl_services, "{$this->tbl_name}.FileServiceID={$this->tbl_services}.ID", "inner")
				->where("{$this->tbl_name}.MemberID", $id)
				->where("{$this->tbl_name}.Status", $status);
					
		return $this->odatatables->generate();
	}
		
	public function get_datatable($access)
	{
		$this->load->library('datatables');
		$oprations = '';
		if($access['edit'] == 'Y')
			$oprations .= '<a href="'.site_url("admin/serverorder/edit/$1").'" title="Edit this record" class="tip"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
		if($access['edit'] == 'Y')
			$oprations .= '<a href="'.site_url("admin/serverorder/cancel/$1").'" title="Cancel this order" class="tip" onclick="return confirm(\'Are you sure to cancel record \');"><i class="fa fa-mail-reply" aria-hidden="true"></i></a>';		
		if($access['delete'] == 'Y')
			$oprations .= '<a href="'.site_url("admin/serverorder/delete/$1").'" title="Delete this record" class="tip" onclick="return confirm(\'Are sure want to delete this record?\');"><i class="fa fa-trash-o" aria-hidden="true"></i></a>';
		
		$this->datatables				
				->select("{$this->tbl_name}.ID, {$this->tbl_name}.Code, {$this->tbl_services}.Title, {$this->tbl_name}.Email, {$this->tbl_name}.Status, {$this->tbl_name}.CreatedDateTime", TRUE)
				->from($this->tbl_name)
				->join($this->tbl_services, "{$this->tbl_name}.ServerServiceID={$this->tbl_services}.ID", "inner")
				//->add_column('select', '<input type="checkbox" name="Chk[]" value="$1" class="chksel">', "{$this->tbl_name}.ID")
				->add_column('delete', $oprations, "ID");
		return $this->datatables->generate();
	}
}
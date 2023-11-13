<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_gl_potongan extends CI_Model
{

    public $table = 'tb_potongan';
    public $id = 'id_potongan';
    public $order = 'DESC';

    function __construct()
    {
        parent::__construct();
    }

    // datatables
    function json() {
        $this->datatables->select('id_potongan,kode_potongan,nama_potongan,nominal,ket_potongan,tgl_ins,tgl_updt,user_ins,user_updt');
        $this->datatables->from('tb_potongan');
        //add this line for join
        //$this->datatables->join('table2', 'tb_potongan.field = table2.field');
        $this->datatables->add_column('action', anchor(site_url('gl-admin-potongan-edit/$1'),'Update')." | ".anchor(site_url('c_gl_admin_potongan/delete/$1'),'Delete','onclick="javasciprt: return confirm(\'Are You Sure ?\')"'), 'id_potongan');
        return $this->datatables->generate();
    }

    // get all
    function get_all()
    {
        $this->db->order_by($this->id, $this->order);
        return $this->db->get($this->table)->result();
    }

    function get_like($nama)
    {
        $this->db->like('nama_potongan', $nama);
        $this->db->order_by($this->id, $this->order);
        return $this->db->get($this->table)->result();
    }

    // get data by id
    function get_by_id($id)
    {
        $this->db->where($this->id, $id);
        return $this->db->get($this->table)->row();
    }

    // get total rows
    function total_rows($q = NULL) {
        $this->db->like('id_potongan', $q);
        $this->db->or_like('kode_potongan', $q);
	$this->db->or_like('nama_potongan', $q);
	$this->db->or_like('nominal', $q);
	$this->db->or_like('ket_potongan', $q);
	$this->db->or_like('tgl_ins', $q);
	$this->db->or_like('tgl_updt', $q);
	$this->db->or_like('user_ins', $q);
	$this->db->or_like('user_updt', $q);
	$this->db->from($this->table);
        return $this->db->count_all_results();
    }

    // get data with limit and search
    function get_limit_data($limit, $start = 0, $q = NULL) {
        $this->db->order_by($this->id, $this->order);
        $this->db->like('id_potongan', $q);
        $this->db->or_like('kode_potongan', $q);
	$this->db->or_like('nama_potongan', $q);
	$this->db->or_like('nominal', $q);
	$this->db->or_like('ket_potongan', $q);
	$this->db->or_like('tgl_ins', $q);
	$this->db->or_like('tgl_updt', $q);
	$this->db->or_like('user_ins', $q);
	$this->db->or_like('user_updt', $q);
	$this->db->limit($limit, $start);
        return $this->db->get($this->table)->result();
    }

    // insert data
    function insert($data)
    {
        $this->db->insert($this->table, $data);
    }

    // update data
    function update($id, $data)
    {
        $this->db->where($this->id, $id);
        $this->db->update($this->table, $data);
    }

    // delete data
    function delete($id)
    {
        $this->db->where($this->id, $id);
        $this->db->delete($this->table);
    }

}

/* End of file M_gl_potongan.php */
/* Location: ./application/models/M_gl_potongan.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-08 06:44:56 */
/* http://harviacode.com */

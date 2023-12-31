<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_gl_gaji_pokok extends CI_Model
{

    public $table = 'tb_gaji_pokok';
    public $id = 'id_gaji_pokok';
    public $order = 'DESC';

    function __construct()
    {
        parent::__construct();
    }

    // datatables
    function json() {
        $this->datatables->select("id_gaji_pokok,A.id_karyawan,no_karyawan,nama_karyawan,nama_jabatan,tanggal,besar_gaji,is_pajak,ptkp,rumus_gaji");
        $this->datatables->from('tb_gaji_pokok A');
        $this->datatables->join('tb_karyawan C', 'A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor','LEFT');
        $this->datatables->join('tb_jabatan B', 'C.id_jabatan = B.id_jabatan AND B.kode_kantor = C.kode_kantor','LEFT');
        $this->datatables->add_column('action', anchor(site_url('gl-admin-gapok-edit/$1'),'Update')." | ".anchor(site_url('c_gl_admin_gaji_pokok/delete/$1'),'Delete','onclick="javasciprt: return confirm(\'Are You Sure ?\')"'), 'id_gaji_pokok');
        return $this->datatables->generate();
    }

    // get all
    function get_all()
    {
        $this->db->order_by($this->id, $this->order);
        return $this->db->get($this->table)->result();
    }

    // get data by id
    function get_by_id($id)
    {
      $this->db->select('A.id_gaji_pokok,A.id_karyawan,no_karyawan,nama_karyawan,tanggal,besar_gaji,A.kode_kantor,A.is_pajak,ptkp,rumus_gaji');
      $this->db->from('tb_gaji_pokok A');
      $this->db->join('tb_karyawan C', 'A.id_karyawan = C.id_karyawan');
      $this->db->where($this->id, $id);
      return $this->db->get()->row();
    }

    function get_gaji_pokok($id_karyawan,$kode_kantor)
    {
        $data = $this->db->query("
            SELECT besar_gaji FROM tb_gaji_pokok
            WHERE id_karyawan = '".$id_karyawan."'
        ")->row();
        if(!empty($data))
        {
            return $data->besar_gaji;
        } else {
            return 0;
        }
        
    }

    // get total rows
    function total_rows($q = NULL) {
        $this->db->like('id_gaji_pokok', $q);
      	$this->db->or_like('id_karyawan', $q);
      	$this->db->or_like('tanggal', $q);
      	$this->db->or_like('besar_gaji', $q);
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
        $this->db->like('id_gaji_pokok', $q);
	$this->db->or_like('id_karyawan', $q);
	$this->db->or_like('tanggal', $q);
	$this->db->or_like('besar_gaji', $q);
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

/* End of file M_gl_gaji_pokok.php */
/* Location: ./application/models/M_gl_gaji_pokok.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-08 06:45:39 */
/* http://harviacode.com */

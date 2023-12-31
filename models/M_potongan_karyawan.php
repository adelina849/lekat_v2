<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_potongan_karyawan extends CI_Model
{

    public $table = 'tb_potongan_karyawan';
    public $id = 'id_potongan_karyawan';
    public $order = 'DESC';

    function __construct()
    {
        parent::__construct();
    }

    // datatables
    function json() {
        $this->datatables->select('A.id_potongan_karyawan,nama_potongan,nama_karyawan,no_karyawan,A.nominal,is_aktif,nama_kantor,A.kode_kantor');
        $this->datatables->from('tb_potongan_karyawan A');
        $this->datatables->join('tb_potongan B', 'A.id_potongan = B.id_potongan','LEFT');
        $this->datatables->join('tb_karyawan C', 'A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor','LEFT');
        $this->datatables->join('tb_kantor D', 'C.kode_kantor = D.kode_kantor','LEFT');
        $this->datatables->add_column('action', anchor(site_url('gl-admin-potongan-karyawan-edit/$1'),'Update')." | ".anchor(site_url('c_gl_admin_potongan_karyawan/delete/$1'),'Delete','onclick="javasciprt: return confirm(\'Are You Sure ?\')"'), 'id_potongan_karyawan');
        return $this->datatables->generate();
    }

    function list_potongan_karyawan($id)
    {
        $data = $this->db->query("
          SELECT B.id_potongan,COALESCE(id_potongan_karyawan,'') AS id_potongan_karyawan,B.kode_potongan,
                 B.nama_potongan,COALESCE(A.nominal,0) nominal,COALESCE(is_aktif,'') is_aktif
          FROM tb_potongan B
          LEFT JOIN tb_potongan_karyawan A
             ON A.id_potongan = B.id_potongan AND A.id_karyawan = '".$id."'
          ORDER BY B.nama_potongan
      ")->result();

      return $data;
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
        $this->db->select('A.id_potongan_karyawan,A.id_potongan,A.id_karyawan,nama_potongan,nama_karyawan,no_karyawan,
                           A.nominal,nama_karyawan,nama_potongan,is_aktif,A.kode_kantor');
        $this->db->from('tb_potongan_karyawan A');
        $this->db->join('tb_potongan B', 'A.id_potongan = B.id_potongan','LEFT');
        $this->db->join('tb_karyawan C', 'A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor','LEFT');
        $this->db->where($this->id, $id);
        return $this->db->get()->row();
    }

    // get total rows
    function total_rows($q = NULL) {
        $this->db->like('id_potongan_karyawan', $q);
	$this->db->or_like('id_karyawan', $q);
	$this->db->or_like('nominal', $q);
	$this->db->or_like('is_aktif', $q);
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
        $this->db->like('id_potongan_karyawan', $q);
	$this->db->or_like('id_karyawan', $q);
	$this->db->or_like('nominal', $q);
	$this->db->or_like('is_aktif', $q);
	$this->db->or_like('tgl_ins', $q);
	$this->db->or_like('tgl_updt', $q);
	$this->db->or_like('user_ins', $q);
	$this->db->or_like('user_updt', $q);
	$this->db->limit($limit, $start);
        return $this->db->get($this->table)->result();
    }

    function delete_potongan($id_karyawan,$kode_kantor)
    {
      $this->db->query("
        DELETE FROM tb_potongan_karyawan
        WHERE id_karyawan = '".$id_karyawan."' AND kode_kantor = '".$kode_kantor."'
      ");
    }

    function set_default($id_karyawan,$user_ins,$kode_kantor)
    {
      $this->db->query("
          INSERT INTO tb_potongan_karyawan (
            id_potongan,
            id_karyawan,
            nominal,
            is_aktif,
            tgl_ins,
            user_ins,
            kode_kantor
          )
          SELECT id_potongan,
                 '".$id_karyawan."' id_karyawan,
                 0 AS nominal,
                 0 is_aktif,
                 NOW() AS tgl_ins,
                 '".$user_ins."' AS user_ins,
                 '".$kode_kantor."' AS kode_kantor
          FROM tb_potongan
        ");
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

/* End of file M_potongan_karyawan.php */
/* Location: ./application/models/M_potongan_karyawan.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-08 06:45:10 */
/* http://harviacode.com */

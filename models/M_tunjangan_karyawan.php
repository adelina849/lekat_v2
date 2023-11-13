<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_tunjangan_karyawan extends CI_Model
{

    public $table = 'tb_tunjangan_karyawan';
    public $id = 'id_tunjangan_karyawan';
    public $order = 'DESC';

    function __construct()
    {
        parent::__construct();
    }

    function list_karyawan($cari)
    {
      $data = $this->db->query("
          SELECT A.*,D.nama_jabatan,DATEDIFF(NOW(),tgl_diterima) / 365 AS masa_kerja
          FROM tb_karyawan A
          LEFT JOIN tb_gaji_pokok B
            ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
          LEFT JOIN tb_karyawan_jabatan C
            ON A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor
          LEFT JOIN tb_jabatan D
            ON C.id_jabatan = D.id_jabatan AND C.kode_kantor = D.kode_kantor
          WHERE isDefault = '1' AND A.isAktif = 'DITERIMA'
          AND nama_karyawan LIKE '%".$cari."%'
      ")->result();

      return $data;
    }

    function list_tunjangan_karyawan($id)
    {
        $data = $this->db->query("
          SELECT B.id_tunjangan,COALESCE(id_tunjangan_karyawan,'') AS id_tunjangan_karyawan,B.kode_tunjangan,
                 B.nama_tunjangan,COALESCE(A.nominal,0) nominal,COALESCE(is_aktif,'') is_aktif
          FROM tb_tunjangan B
          LEFT JOIN tb_tunjangan_karyawan A
             ON A.id_tunjangan = B.id_tunjangan AND A.id_karyawan = '".$id."'
          ORDER BY B.nama_tunjangan
      ")->result();

      return $data;
    }

    // datatables
    function json() {
        $this->datatables->select('A.id_tunjangan_karyawan,nama_tunjangan,nama_karyawan,no_karyawan,A.nominal,is_aktif,nama_kantor,A.kode_kantor');
        $this->datatables->from('tb_tunjangan_karyawan A');
        $this->datatables->join('tb_tunjangan B', 'A.id_tunjangan = B.id_tunjangan','LEFT');
        $this->datatables->join('tb_karyawan C', 'A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor','LEFT');
        $this->datatables->join('tb_kantor D', 'C.kode_kantor = D.kode_kantor','LEFT');
        $this->datatables->add_column('action', anchor(site_url('gl-admin-tunjangan-karyawan-edit/$1'),'Update')." | ".anchor(site_url('c_gl_admin_tunjangan_karyawan/delete/$1'),'Delete','onclick="javasciprt: return confirm(\'Are You Sure ?\')"'), 'id_tunjangan_karyawan');
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
        $this->db->select('A.id_tunjangan_karyawan,A.id_tunjangan,A.id_karyawan,nama_tunjangan,nama_karyawan,no_karyawan,
                           A.nominal,nama_karyawan,nama_tunjangan,is_aktif,A.kode_kantor');
        $this->db->from('tb_tunjangan_karyawan A');
        $this->db->join('tb_tunjangan B', 'A.id_tunjangan = B.id_tunjangan','LEFT');
        $this->db->join('tb_karyawan C', 'A.id_karyawan = C.id_karyawan AND A.kode_kantor = C.kode_kantor','LEFT');
        $this->db->where($this->id, $id);
        return $this->db->get()->row();
    }

    // get total rows
    function total_rows($q = NULL) {
        $this->db->like('id_tunjangan_karyawan', $q);
      	$this->db->or_like('id_tunjangan', $q);
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
        $this->db->like('id_tunjangan_karyawan', $q);
      	$this->db->or_like('id_tunjangan', $q);
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

    function delete_tunjangan($id_karyawan,$kode_kantor)
    {
      $this->db->query("
        DELETE FROM tb_tunjangan_karyawan
        WHERE id_karyawan = '".$id_karyawan."' AND kode_kantor = '".$kode_kantor."'
      ");
    }

    function set_default($id_karyawan,$user_ins,$kode_kantor)
    {
      $this->db->query("
          INSERT INTO tb_tunjangan_karyawan (
            id_tunjangan,
            id_karyawan,
            nominal,
            is_aktif,
            tgl_ins,
            user_ins,
            kode_kantor
          )
          SELECT id_tunjangan,
                 '".$id_karyawan."' id_karyawan,
                 0 AS nominal,
                 0 is_aktif,
                 NOW() AS tgl_ins,
                 '".$user_ins."' AS user_ins,
                 '".$kode_kantor."' AS kode_kantor
          FROM tb_tunjangan
          WHERE nama_tunjangan IN(
            'Tunjangan Jabatan', 'Tunjangan Masa Kerja', 'Tunjangan Make Up', 
            'Uang Makan', 'Insentif Tindakan', 'Ins Ass Dokter / Resep', 
            'Insentif Hari Libur', 'Lembur', 'Fee Upselling', 'Penanggung Jawab', 
            'Kenaikan Gaji', 'Insentif Mutasi', 'Other'
          )
        ");
    }

    function set_default_dokter($id_karyawan,$user_ins,$kode_kantor)
    {
      $this->db->query("
          INSERT INTO tb_tunjangan_karyawan (
            id_tunjangan,
            id_karyawan,
            nominal,
            is_aktif,
            tgl_ins,
            user_ins,
            kode_kantor
          )
          SELECT id_tunjangan,
                 '".$id_karyawan."' id_karyawan,
                 0 AS nominal,
                 0 is_aktif,
                 NOW() AS tgl_ins,
                 '".$user_ins."' AS user_ins,
                 '".$kode_kantor."' AS kode_kantor
          FROM tb_tunjangan
          WHERE nama_tunjangan IN(
            'Ins Lembut Tgl Merah','Ins. Produk Pasien > 1jt','Insentif Hari Libur','Insentif Mutasi','Insentif Promo',
            'Insentif Tindakan','Kenaikan Gaji','Lembur','Other','Pasien Baru','Pasien Lama','Pasien Online','Tindakan 550rb > 15X',
            'Tindakan Lembur','Treatment > 10 Jt','Tunjangan Jabatan','Tunjangan Make Up','Tunjangan Masa Kerja','Uang Makan'
          )
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

/* End of file M_tunjangan_karyawan.php */
/* Location: ./application/models/M_tunjangan_karyawan.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-08 06:44:29 */
/* http://harviacode.com */

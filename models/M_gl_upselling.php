<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_gl_upselling extends CI_Model
{

    public $table = 'tb_h_upselling';
    public $id = 'id_h_upselling';
    public $order = 'DESC';

    function __construct()
    {
        parent::__construct();
    }

    function get_no_upselling()
    {
        $data = $this->db->query("
            SELECT CONCAT('".$this->session->userdata('ses_kode_kantor')."',FRMTGL,ORD) AS no_upselling
            From
            (
                SELECT CONCAT(Y,M,D) AS FRMTGL
                 ,CASE
                    WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
                    WHEN (ORD >= 100 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
                    WHEN (ORD >= 1000 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
                    WHEN ORD >= 10000 THEN CAST(ORD AS CHAR)
                    ELSE CONCAT('0000',CAST(ORD AS CHAR))
                    END As ORD
                From
                (
                    SELECT
                    CAST(LEFT(NOW(),4) AS CHAR) AS Y,
                    CAST(MID(NOW(),6,2) AS CHAR) AS M,
                    MID(NOW(),9,2) AS D,
                    COALESCE(MAX(CAST(RIGHT(no_upselling,5) AS UNSIGNED)) + 1,1) AS ORD
                    From tb_h_upselling
                    WHERE DATE(tgl_ins) = DATE(NOW())
                    AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
                ) AS A
            ) AS AA
        ")->row();

        return $data;
    }

    function list_karyawan($cari,$kode_kantor)
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
          WHERE A.isAktif = 'DITERIMA'
          AND nama_karyawan LIKE '%".$cari."%' AND A.kode_kantor = '".$kode_kantor."'
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
        $this->db->where($this->id, $id);
        return $this->db->get($this->table)->row();
    }

    function list_upselling($kode_kantor,$tgl_from,$tgl_to,$cari)
    {
        $data = $this->db->query("
            SELECT no_costumer,A.id_h_upselling,A.id_costumer,tanggal,GROUP_CONCAT(nama_produk) AS nama_produk,nama_lengkap,foto_bukti,
               A.kode_kantor_cust,nama_karyawan
            FROM tb_h_upselling A
            LEFT JOIN tb_d_upselling P
              ON P.id_h_upselling = A.id_h_upselling
            LEFT JOIN tb_produk B
              ON P.id_produk = B.id_produk AND B.kode_kantor = '".$kode_kantor."'
            LEFT JOIN tb_costumer C
              ON A.id_costumer = C.id_costumer AND A.kode_kantor_cust = C.kode_kantor
            LEFT JOIN tb_karyawan K
              ON A.id_karyawan = K.id_karyawan AND A.kode_kantor = K.kode_kantor
            WHERE A.kode_kantor = '".$kode_kantor."' AND A.tanggal BETWEEN '".$tgl_from."' AND '".$tgl_to."'
                  AND K.nama_karyawan LIKE '%".$cari."%'
            GROUP BY no_costumer,A.id_h_upselling,A.id_costumer,tanggal,nama_lengkap,foto_bukti,A.kode_kantor_cust,nama_karyawan
        ")->result();

        return $data;
    }

    function list_d_upselling($id_h_upselling)
    {
        $data = $this->db->query("
            SELECT A.id_d_upselling,A.id_produk,kode_produk,nama_produk,qty
            FROM tb_d_upselling A
            LEFT JOIN tb_produk B
              ON A.id_produk = B.id_produk AND B.kode_kantor = 'JKT'
            WHERE id_h_upselling = '".$id_h_upselling."'
        ")->result();

        return $data;
    }

    function list_pasien($cari,$cari_kantor)
    {
        $data = $this->db->query("
            SELECT id_costumer,no_costumer,nama_lengkap,kode_kantor
            FROM tb_costumer
            WHERE no_costumer <> '' AND nama_lengkap <> ''
            ".$cari_kantor." AND (nama_lengkap LIKE '%".$cari."%' OR no_costumer LIKE '%".$cari."%')
            ORDER BY nama_lengkap
            LIMIT 10
        ")->result();

        return $data;
    }

    function list_produk($cari)
    {
        $data = $this->db->query("
            SELECT id_produk,kode_produk,nama_produk,isProduk
            FROM tb_produk
            WHERE isProduk IN('PRODUK','JASA') 
            AND kode_kantor = 'JKT'
            AND nama_produk LIKE '%".$cari."%'
            ORDER BY nama_produk,isProduk
            LIMIT 20
        ")->result();

        return $data;
    }

    function list_cabang()
    {
        $data = $this->db->query("
            SELECT kode_kantor FROM tb_kantor
        ")->result();

        return $data;
    }
    
    
    // insert data
    function insert($data)
    {
        $this->db->insert($this->table, $data);

        $return_id = $this->db->insert_id();

        return $return_id;
    }

    function insert_detail($data)
    {
        $this->db->insert('tb_d_upselling', $data);
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

    function delete_detail($id)
    {
        $this->db->where('id_h_upselling', $id);
        $this->db->delete('tb_d_upselling');
    }

    function delete_detail_satuan($id)
    {
        $this->db->where('id_d_upselling', $id);
        $this->db->delete('tb_d_upselling');
    }

}

/* End of file M_gl_upselling.php */
/* Location: ./application/models/M_gl_upselling.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-12-07 05:12:52 */
/* http://harviacode.com */
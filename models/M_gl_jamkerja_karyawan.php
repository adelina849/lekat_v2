<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_gl_jamkerja_karyawan extends CI_Model
{

    function __construct()
    {
        parent::__construct();
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
          AND nama_karyawan LIKE '%".$cari."%' AND A.kode_kantor LIKE '%".$kode_kantor."%'

          
      ")->result();

      return $data;
    }

    function get_all($id_karyawan,$tgl1,$tgl2,$kode_kantor)
    {
       $data = $this->db->query("
                          SELECT * FROM
                          (
                            SELECT id_karyawan,
                                   CASE WHEN nama_jam = 'LIBUR' THEN nama_jam 
                                   WHEN nama_jam = 'DINAS LUAR' THEN nama_jam
                                   WHEN nama_jam = 'Sakit SKD' THEN nama_jam
                                   WHEN nama_jam = 'Sakit Tanpa SKD' THEN nama_jam
                                   WHEN nama_jam = 'Izin' THEN nama_jam
                                   WHEN nama_jam = 'CUTI' THEN nama_jam
                                   WHEN nama_jam = 'Absen' THEN nama_jam
                                   WHEN nama_jam = 'Libur Hari Raya' THEN nama_jam
                                   WHEN nama_jam = 'Dirumahkan' THEN nama_jam
                                   WHEN nama_jam = 'Libur HR Potong' THEN nama_jam
                                   ELSE 'Masuk' END AS title,
                                   CONCAT(tanggal,' ',jam_masuk) AS `start`,
                                   COALESCE(jam_lembur,0) as jam_lembur
                            FROM tb_jam_kerja_karyawan A
                            WHERE id_karyawan = '".$id_karyawan."' AND kode_kantor = '".$kode_kantor."'
                            AND tanggal BETWEEN '".$tgl1."' AND '".$tgl2."'
                            UNION ALL
                            SELECT id_karyawan,
                                   CASE WHEN nama_jam = 'LIBUR' THEN nama_jam 
                                   WHEN nama_jam = 'DINAS LUAR' THEN nama_jam
                                   WHEN nama_jam = 'Sakit SKD' THEN nama_jam
                                   WHEN nama_jam = 'Sakit Tanpa SKD' THEN nama_jam
                                   WHEN nama_jam = 'Izin' THEN nama_jam
                                   WHEN nama_jam = 'CUTI' THEN nama_jam
                                   WHEN nama_jam = 'Absen' THEN nama_jam
                                   WHEN nama_jam = 'Libur Hari Raya' THEN nama_jam
                                   WHEN nama_jam = 'Dirumahkan' THEN nama_jam
                                   WHEN nama_jam = 'Libur HR Potong' THEN nama_jam
                                   ELSE 'Keluar' END AS title,
                                   CONCAT(tanggal,' ',jam_keluar) AS `start`,
                                   COALESCE(jam_lembur,0) as jam_lembur
                            FROM tb_jam_kerja_karyawan A
                            WHERE id_karyawan = '".$id_karyawan."' AND kode_kantor = '".$kode_kantor."'
                            AND tanggal BETWEEN '".$tgl1."' AND '".$tgl2."'
                          ) A ORDER BY `start`
       ")->result();
       return $data;
    }

    function list_shift_karyawan($kode_kantor,$cari)
    {

      $data = $this->db->query("
        SELECT A.id_karyawan,no_karyawan,nama_karyawan,A.kode_kantor,B.id_jam,
               COALESCE(id_shift_karyawan,'') id_shift_karyawan,COALESCE(nama_jam,'') nama_jam
        FROM tb_karyawan A
        LEFT JOIN tb_shift_karyawan B
          ON A.id_karyawan = B.id_karyawan AND B.kode_kantor LIKE '%".$kode_kantor."%'
        LEFT JOIN tb_jam_kerja C
          ON B.id_jam = C.id_jam
        WHERE nama_karyawan LIKE '%".$cari."%'
        AND A.isAktif = 'DITERIMA' AND A.kode_kantor LIKE '%".$kode_kantor."%'
        ORDER BY nama_karyawan
      ")->result();

      return $data;
    }


    function simpan_jam_kerja_monthly($id_karyawan,$kode_kantor,$tanggal)
    {
      $this->db->query("
          INSERT INTO tb_jam_kerja_karyawan (
            id_karyawan, 
            tanggal, 
            nama_jam, 
            jam_masuk, 
            jam_keluar, 
            jam_lembur,
            user_ins, 
            kode_kantor
          )
          SELECT A.id_karyawan,'".$tanggal."' tanggal ,nama_jam,jam_masuk,jam_keluar,0, '".$this->session->userdata('ses_id_karyawan')."' user_ins,'".$kode_kantor."' kode_kantor
          FROM tb_shift_karyawan A
          LEFT JOIN tb_jam_kerja B ON A.id_jam = B.id_jam
          LEFT JOIN tb_karyawan C ON A.id_karyawan = C.id_karyawan AND C.kode_kantor = '".$kode_kantor."'
          WHERE A.id_karyawan = '".$id_karyawan."' AND A.kode_kantor = '".$kode_kantor."'
      ");
    }

    function get_karyawan($id_karyawan,$kode_kantor)
    {
      $data = $this->db->query("
            SELECT * FROM tb_karyawan
            WHERE id_karyawan = '".$id_karyawan."' AND kode_kantor = '".$kode_kantor."'
      ")->row();

      return $data;
    }

    function cek_jamkerja($id_karyawan,$kode_kantor,$tanggal)
    {
      $data = $this->db->query("
          SELECT * FROM tb_jam_kerja_karyawan
          WHERE id_karyawan = '".$id_karyawan."'
          AND tanggal = '".$tanggal."' AND kode_kantor = '".$kode_kantor."'
      ")->row();

      return $data;
    }

    function list_jamkerja()
    {
      $data = $this->db->query("
            SELECT * FROM tb_jam_kerja
      ")->result();

      return $data;
    }

    function list_jamkerja_karyawan($tgl,$id_karyawan,$kode_kantor,$cari)
    {
      $data = $this->db->query("
            SELECT A.id_jam,A.jam_masuk,A.jam_keluar,
                   COALESCE(B.id_jam_karyawan,'') id_jam_karyawan,A.nama_jam,COALESCE(jam_lembur,0) AS jam_lembur
            FROM tb_jam_kerja A
            LEFT JOIN tb_jam_kerja_karyawan B
              ON A.nama_jam = B.nama_jam AND B.tanggal = '".$tgl."' AND B.id_karyawan = '".$id_karyawan."'
                 AND B.kode_kantor = '".$kode_kantor."'
            WHERE A.nama_jam LIKE '%".$cari."%'
      ")->result();

      return $data;
    }

    function insert_jamkerja($data)
    {
      $this->db->insert('tb_jam_kerja_karyawan',$data);
    }

    function update_jamkerja($data,$id_karyawan,$kode_kantor,$tanggal)
    {
      $this->db->where(array('id_karyawan' => $id_karyawan,'kode_kantor'=> $kode_kantor,'tanggal'=>$tanggal))
               ->update('tb_jam_kerja_karyawan',$data);
    }

    function delete_jamkerja($data)
    {
      $this->db->where($data)
               ->delete('tb_jam_kerja_karyawan');
    }

    function delete_jamkerja_bulanan($id_karyawan,$periode,$kode_kantor)
    {
      $this->db->query("
        DELETE FROM tb_jam_kerja_karyawan
        WHERE id_karyawan = '".$id_karyawan."' AND DATE_FORMAT(tanggal,'%Y-%m') = '".$periode."'
        AND kode_kantor = '".$kode_kantor."'
      ");
    }

    function insert_tb_shift_karyawan($data)
    {
      $data = $this->db->insert('tb_shift_karyawan',$data);
    }

    function update_tb_shift_karyawan($data,$id)
    {
      $data = $this->db->where('id_shift_karyawan',$id)
                       ->update('tb_shift_karyawan',$data);
    }

    function cek_shift_karyawan($data)
    {
      $data = $this->db->where($data)
                       ->get('tb_shift_karyawan')->row(); 
      return $data;
    }
}

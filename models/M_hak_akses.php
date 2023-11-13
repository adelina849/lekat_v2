<?php
	Class M_hak_akses extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}
		
		function list_hak_akses_limit($id_jabatan,$cari,$limit,$offset)
		{
			$query = $this->db->query("
										SELECT A.id_fasilitas,A.group1,A.main_group,A.sub_group,A.nama_fasilitas,A.keterangan,COALESCE(B.JUMLAH,0) AS JUMLAH
										FROM tb_fasilitas AS A
										LEFT JOIN
										(
											SELECT id_fasilitas,COUNT(id_hak_akses) AS JUMLAH 
											FROM tb_hak_akses
											WHERE id_jabatan = ".$id_jabatan."
											GROUP BY id_fasilitas
										) AS B
										ON A.id_fasilitas = B.id_fasilitas AND A.kode_kantor = B.kode_kantor
										".$cari." ORDER BY  COALESCE(B.JUMLAH,0) DESC,id_fasilitas ASC,nama_fasilitas ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_hak_akses_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.id_fasilitas) AS JUMLAH
										FROM tb_fasilitas AS A
										".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function get_akses_fasilitas($id_jabatan,$id_fasilitas)
        {
            $query = $this->db->query('SELECT * FROM tb_hak_akses WHERE id_fasilitas = '.$id_fasilitas.' AND id_jabatan = '.$id_jabatan.';');
            if($query->num_rows() > 0)
            {
                return $query->row();
            }
            else
            {
                return false;
            }
        }
		
		function simpan($id_jabatan,$id_fasilitas,$id_user,$kode_kantor)
		{
			$id = date('ymdHis'); 
			$date = date('Y-m-d'); 
			$jam = date('Y-m-d H:i:s'); 
			$data = array
			(
			   'id_jabatan' => $id_jabatan,
			   'id_fasilitas' => $id_fasilitas,
			   'tgl_updt' => $jam,
			   'user_updt' => $id_user,
			   'kode_kantor' => $kode_kantor
			);

			$this->db->insert('tb_hak_akses', $data); 
		}
		
		
		function hapus($id_jabatan,$id_fasilitas)
		{
			$this->db->query('DELETE FROM tb_hak_akses WHERE id_jabatan = '.$id_jabatan.' AND id_fasilitas = '.$id_fasilitas);
		}
	}
?>
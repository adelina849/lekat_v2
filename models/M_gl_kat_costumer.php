<?php
	class M_gl_kat_costumer extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_kat_costumer_limit($cari,$limit,$offset)
		{
			$query =
			"
				SELECT *
					,CASE 
					WHEN (DATE(NOW()) >= DATE(point_dari)) AND DATE(NOW()) <= DATE(point_sampai) THEN
						1
					ELSE
						0
					END AS isPoint
				FROM tb_kat_costumer ".$cari." 
				ORDER BY nama_kat_costumer ASC 
				LIMIT ".$offset.",".$limit."
			";
			
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_kat_costumer_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_kat_costumer) AS JUMLAH FROM tb_kat_costumer ".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function simpan
		(
			$set_default,
			$nama_kat_costumer,
			$status,
			$optr_harga,
			$harga,
			$hirarki_harga,
			$minimal_belanja,
			$kondisi,
			$kelipatan,
			$besar_point,
			$aktif,
			$point_dari,
			$point_sampai,
			$point_fixed,
			$ket_kat_costumer,
			$rumus_harga,
			$rumus_harga3,
			$rumus_harga2,
			$pembulatan,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_kat_costumer
				(
					id_kat_costumer,
					set_default,
					nama_kat_costumer,
					status,
					optr_harga,
					harga,
					hirarki_harga,
					minimal_belanja,
					kondisi,
					kelipatan,
					besar_point,
					aktif,
					point_dari,
					point_sampai,
					point_fixed,
					ket_kat_costumer,
					rumus_harga,
					rumus_harga3,
					rumus_harga2,
					pembulatan,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_kat_costumer
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
								COALESCE(MAX(CAST(RIGHT(id_kat_costumer,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kat_costumer
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$set_default."',
					'".$nama_kat_costumer."',
					'".$status."',
					'".$optr_harga."',
					'".$harga."',
					'".$hirarki_harga."',
					'".$minimal_belanja."',
					'".$kondisi."',
					'".$kelipatan."',
					'".$besar_point."',
					'".$aktif."',
					'".$point_dari."',
					'".$point_sampai."',
					'".$point_fixed."',
					'".$ket_kat_costumer."',
					'".$rumus_harga."',
					'".$rumus_harga3."',
					'".$rumus_harga2."',
					'".$pembulatan."',
					NOW(), 
					NOW(), 
					'".$user_updt."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_kat_costumer,
			$set_default,
			$nama_kat_costumer,
			$status,
			$optr_harga,
			$harga,
			$hirarki_harga,
			$minimal_belanja,
			$kondisi,
			$kelipatan,
			$besar_point,
			$aktif,
			$point_dari,
			$point_sampai,
			$point_fixed,
			$ket_kat_costumer,
			$rumus_harga,
			$rumus_harga3,
			$rumus_harga2,
			$pembulatan,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_kat_costumer SET
						
						set_default = '".$set_default."',
						nama_kat_costumer = '".$nama_kat_costumer."',
						status = '".$status."',
						optr_harga = '".$optr_harga."',
						harga = '".$harga."',
						hirarki_harga = '".$hirarki_harga."',
						minimal_belanja = '".$minimal_belanja."',
						kondisi = '".$kondisi."',
						kelipatan = '".$kelipatan."',
						besar_point = '".$besar_point."',
						aktif = '".$aktif."',
						point_dari = '".$point_dari."',
						point_sampai = '".$point_sampai."',
						point_fixed = '".$point_fixed."',
						ket_kat_costumer = '".$ket_kat_costumer."',
						rumus_harga = '".$rumus_harga."',
						rumus_harga3 = '".$rumus_harga3."',
						rumus_harga2 = '".$rumus_harga2."',
						pembulatan = '".$pembulatan."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_kat_costumer = '".$id_kat_costumer
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_kat_costumer)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_kat_costumer WHERE ".$berdasarkan." = '".$id_kat_costumer."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_kat_costumer($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kat_costumer', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_kat_costumer_cari($cari)
        {
			$query = 
				"
					SELECT * FROM tb_kat_costumer ".$cari."
				";
				
            //$query = $this->db->get_where('tb_kat_costumer', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			$query = $this->db->query($query);
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
	}
?>
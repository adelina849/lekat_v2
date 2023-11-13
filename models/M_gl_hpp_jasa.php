<?php
	class M_gl_hpp_jasa extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_produk($id_produk, $cari,$order_by,$limit,$offset)
		{
			$query = 
				"
					SELECT 
						A.* 
					FROM tb_produk AS A
					LEFT JOIN tb_hpp_jasa AS B ON A.id_produk = B.id_produk_hpp AND A.kode_kantor = B.kode_kantor AND B.id_produk = '".$id_produk."'
					".$cari."
					".$order_by."
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
		
		function list_assets($id_produk, $cari,$order_by,$limit,$offset)
		{
			$query = 
				"
					SELECT
						A.*
					FROM tb_d_assets AS A
					LEFT JOIN tb_hpp_jasa AS B ON A.id_d_assets  = B.id_assets AND A.kode_kantor = B.kode_kantor AND B.id_produk = '".$id_produk."'
					".$cari."
					".$order_by."
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
		
		function list_hpp_jasa_limit($cari,$order_by,$limit,$offset)
		{
			$query = 
				"
					SELECT
						A.*
						,A.banyaknya * A.harga AS SUBTOTAL
						,COALESCE(B.kode_produk,'') AS KODE_PRODUK
						,COALESCE(B.nama_produk,'') AS NAMA_PRODUK
						,COALESCE(C.kode_assets,'') AS KODE_ASSETS
						,COALESCE(C.nama_assets,'') AS NAMA_ASSETS
					FROM tb_hpp_jasa AS A
					LEFT JOIN tb_produk AS B ON A.id_produk_hpp = B.id_produk AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_d_assets AS C ON A.id_assets = C.id_d_assets AND A.kode_kantor = C.kode_kantor
					".$cari."
					".$order_by."
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
		
		function count_hpp_jasa_limit($cari)
		{
			$query = 
					"
						SELECT
							COUNT(A.id_hpp_jasa) AS JUMLAH
						FROM tb_hpp_jasa AS A
						LEFT JOIN tb_produk AS B ON A.id_produk_hpp = B.id_produk AND A.kode_kantor = B.kode_kantor
						LEFT JOIN tb_d_assets AS C ON A.id_assets = C.id_d_assets AND A.kode_kantor = C.kode_kantor
						".$cari."
					";
			$query = $this->db->query($query);
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
			$id_produk,
			$id_produk_hpp,
			$id_assets,
			$banyaknya,
			$satuan,
			$harga,
			$ket_hpp,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_hpp_jasa
				(
					id_hpp_jasa,
					id_produk,
					id_produk_hpp,
					id_assets,
					banyaknya,
					satuan,
					harga,
					ket_hpp,
					user_ins,
					user_updt,
					tgl_ins,
					tgl_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_hpp_jasa
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
								COALESCE(MAX(CAST(RIGHT(id_hpp_jasa,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_hpp_jasa
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_produk."',
					'".$id_produk_hpp."',
					'".$id_assets."',
					'".$banyaknya."',
					'".$satuan."',
					'".$harga."',
					'".$ket_hpp."',
					'".$user_ins."',
					'',
					NOW(),
					NOW(),
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_hpp_jasa,
			$id_produk,
			$id_produk_hpp,
			$id_assets,
			$banyaknya,
			$satuan,
			$harga,
			$ket_hpp,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_hpp_jasa SET
						id_produk_hpp = '".$id_produk_hpp."',
						id_assets = '".$id_assets."',
						banyaknya = '".$banyaknya."',
						satuan = '".$satuan."',
						harga = '".$harga."',
						ket_hpp = '".$ket_hpp."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_hpp_jasa = '".$id_hpp_jasa
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_hpp_jasa)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_hpp_jasa WHERE ".$berdasarkan." = '".$id_hpp_jasa."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_hpp_jasa($id_produk)
		{
			$strquery = "
					UPDATE tb_produk SET hpp = (SELECT SUM(banyaknya * harga) AS HPP FROM tb_hpp_jasa WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$id_produk."') 
					WHERE  kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$id_produk."' ";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_hpp_jasa($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_hpp_jasa', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_hpp_jasa_cari($cari)
        {
			$query = "SELECT * FROM tb_hpp_jasa ".$cari."";
			
            //$query = $this->db->get_where('tb_hpp_jasa', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
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
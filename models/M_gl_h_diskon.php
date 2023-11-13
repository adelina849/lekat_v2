<?php
	class M_gl_h_diskon extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function cek_kategori_produk($kode_kantor,$id_produk)
		{
			$strIdKatProduk = '';
			$query =
					"
						SELECT * FROM tb_prod_to_kat WHERE kode_kantor = '".$kode_kantor."' AND id_produk = '".$id_produk."';
					";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
				//$list_id_kategori = $query;
				/*
				$list_result = $query->result();
				foreach($list_result as $row)
				{
					$strIdKatProduk = $strIdKatProduk."'".$row->id_kat_produk."',";
				}
				*/
			}
			else
			{
				return false;
				//$strIdKatProduk = '';
			}
		}
		
		function cek_diskon_kategori($kode_kantor, $kategori, $id_kat_costumer, $besar_pembelian_satuan, $satuan_jual, $harga)
		{
			/*DISKON SEBENARNYA ADA YANG KURANG, KETIKA PRODUK DI HITUNGNYA MASIH DARI ITEM, BELUM TERCOVER UNTUK NOMINAL*/
			$query = 
			"
				SELECT * 
					,CASE 
						WHEN optr_diskon = '%' THEN
							((".$harga."/100) * besar_diskon)
						WHEN (optr_diskon = 'Produk') OR (optr_diskon = 'Paket') THEN
							0
						ELSE
							besar_diskon
						END
						AS BESAR_DISKON_NOMINAL
					,CASE 
						WHEN optr_diskon = '%' THEN
							".$harga." - ((".$harga."/100) * besar_diskon)
						ELSE
							".$harga."
						END
						AS BESAR_DISKON_FIX
					,".$harga." AS harga_jual
				FROM tb_h_diskon AS A
				WHERE 
				A.jenis = 'KATEGORI'
				AND A.id_kat_produk IN (".$kategori.")
				AND (A.id_kat_costumer = '".$id_kat_costumer."' OR A.id_kat_costumer = '')
				AND CASE 
						WHEN optr_kondisi = '==' THEN
							".$besar_pembelian_satuan." = besar_pembelian
						WHEN optr_kondisi = '>=' THEN
							".$besar_pembelian_satuan." >= besar_pembelian
						WHEN optr_kondisi = '>' THEN
							".$besar_pembelian_satuan." > besar_pembelian
						WHEN optr_kondisi = '<' THEN
							".$besar_pembelian_satuan." < besar_pembelian
						WHEN optr_kondisi = '<=' THEN
							".$besar_pembelian_satuan." <= besar_pembelian
						END
				-- AND satuan_diskon = '".$satuan_jual."'
				AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai)
				AND set_default = '1'
				AND kode_kantor = '".$kode_kantor."'
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
		
		function cek_diskon_produk($kode_kantor, $id_produk, $id_kat_costumer, $besar_pembelian_satuan, $satuan_jual, $harga)
		{
			/*DISKON SEBENARNYA ADA YANG KURANG, KETIKA PRODUK DI HITUNGNYA MASIH DARI ITEM, BELUM TERCOVER UNTUK NOMINAL*/
			$query = 
			"
				SELECT 
					A.* 
					,CASE 
						WHEN A.optr_diskon = '%' THEN
							((".$harga."/100) * A.besar_diskon)
						WHEN (A.optr_diskon = 'Produk') OR (A.optr_diskon = 'Paket') THEN
							0
						ELSE
							A.besar_diskon
						END
						AS BESAR_DISKON_NOMINAL
					,CASE 
						WHEN A.optr_diskon = '%' THEN
							".$harga." - ((".$harga."/100) * A.besar_diskon)
						ELSE
							".$harga."
						END
						AS BESAR_DISKON_FIX
					,".$harga." AS harga_jual
					-- ,COALESCE(B.harga_jual,0) AS harga_modal_dari_diskon
				FROM tb_h_diskon AS A
				
				/*
				LEFT JOIN
				(
					SELECT 
						A.*
						,COALESCE(B.kode_satuan,'') AS kode_satuan
						,COALESCE(B.nama_satuan,'') AS nama_satuan
					FROM tb_satuan_konversi AS A
					INNER JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
				) AS B ON A.kode_kantor = B.kode_kantor AND A.satuan_diskon = B.kode_satuan
				*/
				
				WHERE
				CASE WHEN A.jenis = 'KATEGORI' THEN
					A.id_kat_produk IN (SELECT id_kat_produk FROM tb_prod_to_kat WHERE id_produk = '".$id_produk."' AND kode_kantor = '".$kode_kantor."' GROUP BY id_kat_produk)
				ELSE
					A.id = '".$id_produk."' 
				END
				AND A.jenis IN ('PRODUK','KATEGORI')
				AND (A.id_kat_costumer = '".$id_kat_costumer."' OR A.id_kat_costumer = '')
				AND CASE 
						WHEN A.optr_kondisi = '==' THEN
							".$besar_pembelian_satuan." = besar_pembelian
						WHEN A.optr_kondisi = '>=' THEN
							".$besar_pembelian_satuan." >= besar_pembelian
						WHEN A.optr_kondisi = '>' THEN
							".$besar_pembelian_satuan." > besar_pembelian
						WHEN A.optr_kondisi = '<' THEN
							".$besar_pembelian_satuan." < besar_pembelian
						WHEN A.optr_kondisi = '<=' THEN
							".$besar_pembelian_satuan." <= besar_pembelian
						END
				-- AND satuan_diskon = '".$satuan_jual."'
				
				AND CASE WHEN A.jenis = 'KATEGORI' THEN
					A.satuan_diskon LIKE '%%'
				ELSE
					A.satuan_diskon = '".$satuan_jual."' 
				END
				
				AND DATE(NOW()) >= DATE(A.dari) AND DATE(NOW()) <= DATE(A.sampai)
				AND A.set_default = '1'
				AND A.kode_kantor = '".$kode_kantor."'
			";
			
			
			
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				/*
				$query2 = 
				"
					SELECT * 
						,CASE 
							WHEN optr_diskon = '%' THEN
								((".$harga."/100) * besar_diskon)
							ELSE
								besar_diskon
							END
							AS BESAR_DISKON_NOMINAL
						,CASE 
							WHEN optr_diskon = '%' THEN
								".$harga." - ((".$harga."/100) * besar_diskon)
							ELSE
								besar_diskon
							END
							AS BESAR_DISKON_FIX
					FROM tb_h_diskon AS A
					WHERE 
					A.id = '".$id_produk."'
					AND CASE 
							WHEN optr_kondisi = '==' THEN
								".$besar_pembelian_satuan." = besar_pembelian
							WHEN optr_kondisi = '>=' THEN
								".$besar_pembelian_satuan." >= besar_pembelian
							WHEN optr_kondisi = '>' THEN
								".$besar_pembelian_satuan." > besar_pembelian
							WHEN optr_kondisi = '<' THEN
								".$besar_pembelian_satuan." < besar_pembelian
							WHEN optr_kondisi = '<=' THEN
								".$besar_pembelian_satuan." <= besar_pembelian
							END
					AND satuan_diskon = '".$satuan_jual."'
					AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai)
					AND set_default = '1'
					AND kode_kantor = '".$kode_kantor."'
				";
				
				$query2 = $this->db->query($query2);
				if($query2->num_rows() > 0)
				{
					return $query2;
				}
				else
				{
				*/
					return false;
				//}
			}
		}
		
		function cek_diskon_akumulasi($kode_kantor, $id_kat_costumer, $besar_pembelian_satuan, $besar_pembelian_nominal)
		{
			if($besar_pembelian_nominal == "")
			{
				$besar_pembelian_nominal = 0;
			}
			
			if($besar_pembelian_satuan == "")
			{
				$besar_pembelian_satuan = 0;
			}
			
			$query = 
			"
				SELECT * 
					,CASE 
						WHEN optr_diskon = '%' THEN
							((".$besar_pembelian_nominal."/100) * besar_diskon)
						WHEN (optr_diskon = 'Produk') OR (optr_diskon = 'Paket') THEN
							0
						ELSE
							besar_diskon
						END
						AS BESAR_DISKON_NOMINAL
					,CASE 
						WHEN optr_diskon = '%' THEN
							".$besar_pembelian_nominal." - ((".$besar_pembelian_nominal."/100) * besar_diskon)
						ELSE
							".$besar_pembelian_nominal."
						END
						AS BESAR_DISKON_FIX
				FROM tb_h_diskon AS A
				WHERE 
				id = ''
				-- AND optr_diskon NOT IN ('Paket','Produk')
				AND jenis = 'AKUMULASI'
				AND (A.id_kat_costumer = '".$id_kat_costumer."' OR A.id_kat_costumer = '')
				AND 
					CASE 
						WHEN satuan = 'C' THEN
							CASE 
							WHEN optr_kondisi = '==' THEN
								".$besar_pembelian_nominal." = besar_pembelian
							WHEN optr_kondisi = '>=' THEN
								".$besar_pembelian_nominal." >= besar_pembelian
							WHEN optr_kondisi = '>' THEN
								".$besar_pembelian_nominal." > besar_pembelian
							WHEN optr_kondisi = '<' THEN
								".$besar_pembelian_nominal." < besar_pembelian
							WHEN optr_kondisi = '<=' THEN
								".$besar_pembelian_nominal." <= besar_pembelian
							END
						ELSE
							CASE 
							WHEN optr_kondisi = '==' THEN
								".$besar_pembelian_satuan." = besar_pembelian
							WHEN optr_kondisi = '>=' THEN
								".$besar_pembelian_satuan." >= besar_pembelian
							WHEN optr_kondisi = '>' THEN
								".$besar_pembelian_satuan." > besar_pembelian
							WHEN optr_kondisi = '<' THEN
								".$besar_pembelian_satuan." < besar_pembelian
							WHEN optr_kondisi = '<=' THEN
								".$besar_pembelian_satuan." <= besar_pembelian
							END
						END
				AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai)
				AND set_default = '1'
				AND kode_kantor = '".$kode_kantor."'
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
		
		function list_h_diskon_limit($cari,$limit,$offset)
		{
			$query = 
			"
				
				SELECT
					A.id_h_diskon,
					A.id_kat_costumer,
					A.id,group_by,
					A.nama_diskon,
					A.besar_diskon,
					A.optr_diskon,
					A.ket_diskon,
					A.dari,
					A.sampai,
					A.satuan,
					A.satuan_diskon,
					A.optr_kondisi,
					A.besar_pembelian,
					A.set_default,
					A.jenis,
					A.id_kat_produk,
					A.kode_kat_produk,
					A.nama_kat_produk,
					A.tgl_ins,
					A.tgl_updt
					,A.user_updt
					,A.user_ins
					,A.kode_kantor
					,A.kode_produk
					,A.nama_produk
					,A.nama_kat_costumer
					,A.JUMLAH_PRODUKS
					,GROUP_CONCAT(DISTINCT hari ORDER BY hari ASC SEPARATOR ', ') AS HARI_AKTIF
				FROM
				(
					SELECT  
						A.id_h_diskon,
						A.id_kat_costumer,
						A.id,group_by,
						A.nama_diskon,
						A.besar_diskon,
						A.optr_diskon,
						A.ket_diskon,
						A.dari,
						A.sampai,
						A.satuan,
						A.satuan_diskon,
						A.optr_kondisi,
						A.besar_pembelian,
						A.set_default,
						A.jenis,
						A.id_kat_produk,
						A.kode_kat_produk,
						A.nama_kat_produk,
						A.tgl_ins,
						A.tgl_updt
						,A.user_updt
						,A.user_ins
						,A.kode_kantor
						,COALESCE(B.kode_produk,'') AS kode_produk
						,COALESCE(B.nama_produk,'') AS nama_produk
						,COALESCE(C.nama_kat_costumer,'') AS nama_kat_costumer
						,COALESCE(D.JUMLAH,0) AS JUMLAH_PRODUKS
						,COALESCE(E.hari,'') AS hari
					FROM tb_h_diskon AS A 
					LEFT JOIN tb_produk AS B ON A.id = B.id_produk AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kat_costumer AS C ON A.id_kat_costumer = C.id_kat_costumer AND A.kode_kantor = C.kode_kantor
					LEFT JOIN 
					(
						SELECT COUNT(id_d_diskon) AS JUMLAH, id_h_diskon,kode_kantor
						FROM tb_d_diskon
						GROUP BY id_h_diskon,kode_kantor
					) AS D ON A.id_h_diskon = D.id_h_diskon AND A.kode_kantor = D.kode_kantor
					LEFT JOIN tb_hari_diskon AS E ON A.id_h_diskon = E.id_h_diskon AND A.kode_kantor = E.kode_kantor
					
					".$cari." LIMIT ".$offset.",".$limit."
				) AS A
				GROUP BY A.id_h_diskon,
					A.id_kat_costumer,
					A.id,group_by,
					A.nama_diskon,
					A.besar_diskon,
					A.optr_diskon,
					A.ket_diskon,
					A.dari,
					A.sampai,
					A.satuan,
					A.satuan_diskon,
					A.optr_kondisi,
					A.besar_pembelian,
					A.set_default,
					A.jenis,
					A.id_kat_produk,
					A.kode_kat_produk,
					A.nama_kat_produk,
					A.tgl_ins,
					A.tgl_updt
					,A.user_updt
					,A.user_ins
					,A.kode_kantor
					,A.kode_produk
					,A.nama_produk
					,A.nama_kat_costumer
					,A.JUMLAH_PRODUKS
				ORDER BY A.tgl_ins DESC 
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
		
		function count_h_diskon_limit($cari)
		{
			$query = 
			"
				SELECT COUNT(A.id_h_diskon) AS JUMLAH FROM tb_h_diskon AS A LEFT JOIN tb_produk AS B ON A.id = B.id_produk AND A.kode_kantor = B.kode_kantor ".$cari."
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
			$id_kat_costumer,
			$id,
			$group_by,
			$nama_diskon,
			$besar_diskon,
			$optr_diskon,
			$ket_diskon,
			$dari,
			$sampai,
			$satuan,
			$satuan_diskon,
			$optr_kondisi,
			$besar_pembelian,
			$set_default,
			
			$isAkumulasi,
			$id_kat_produk,
			$kode_kat_produk,
			$nama_kat_produk,
			
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_h_diskon
				(
					id_h_diskon,
					id_kat_costumer,
					id,
					group_by,
					nama_diskon,
					besar_diskon,
					optr_diskon,
					ket_diskon,
					dari,
					sampai,
					satuan,
					satuan_diskon,
					optr_kondisi,
					besar_pembelian,
					set_default,
					
					jenis,
					id_kat_produk,
					kode_kat_produk,
					nama_kat_produk,
					
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_diskon
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
								COALESCE(MAX(CAST(RIGHT(id_h_diskon,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_h_diskon
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_kat_costumer."',
					'".$id."',
					'".$group_by."',
					'".$nama_diskon."',
					'".$besar_diskon."',
					'".$optr_diskon."',
					'".$ket_diskon."',
					'".$dari."',
					'".$sampai."',
					'".$satuan."',
					'".$satuan_diskon."',
					'".$optr_kondisi."',
					'".$besar_pembelian."',
					'".$set_default."',
					
					'".$isAkumulasi."',
					'".$id_kat_produk."',
					'".$kode_kat_produk."',
					'".$nama_kat_produk."',
					
					NOW(),
					NOW(),
					'',
					'".$user_ins."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_h_diskon,
			$id_kat_costumer,
			$id,
			$group_by,
			$nama_diskon,
			$besar_diskon,
			$optr_diskon,
			$ket_diskon,
			$dari,
			$sampai,
			$satuan,
			$satuan_diskon,
			$optr_kondisi,
			$besar_pembelian,
			$set_default,
			
			$isAkumulasi,
			$id_kat_produk,
			$kode_kat_produk,
			$nama_kat_produk,
			
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_h_diskon SET
					
						id_kat_costumer = '".$id_kat_costumer."',
						id = '".$id."',
						group_by = '".$group_by."',
						nama_diskon = '".$nama_diskon."',
						besar_diskon = '".$besar_diskon."',
						optr_diskon = '".$optr_diskon."',
						ket_diskon = '".$ket_diskon."',
						dari = '".$dari."',
						sampai = '".$sampai."',
						satuan = '".$satuan."',
						satuan_diskon = '".$satuan_diskon."',
						optr_kondisi = '".$optr_kondisi."',
						besar_pembelian = '".$besar_pembelian."',
						
						jenis = '".$isAkumulasi."',
						id_kat_produk = '".$id_kat_produk."',
						kode_kat_produk = '".$kode_kat_produk."',
						nama_kat_produk = '".$nama_kat_produk."',
						
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'

					WHERE kode_kantor = '".$kode_kantor."' AND id_h_diskon = '".$id_h_diskon
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_h_diskon)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_h_diskon WHERE ".$berdasarkan." = '".$id_h_diskon."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_h_diskon($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_h_diskon', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_h_diskon_cari($cari)
        {
			$strquery =
			"
				SELECT * FROM tb_h_diskon ".$cari."
			";
			
            //$query = $this->db->get_where('tb_h_penjualan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            $query = $this->db->query($strquery);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
        }
		
		function get_d_diskon_cari($cari)
        {
			$strquery =
			"
				SELECT 
					A.*
					,COALESCE(B.kode_satuan,'') AS kode_satuan 
					,COALESCE(B.nama_satuan,'') AS nama_satuan 
					,COALESCE(B.status,'') AS status 
					,COALESCE(B.besar_konversi,0) AS besar_konversi 
					,COALESCE(B.harga_jual,0) AS modal 
					,COALESCE(C.isProduk,'') AS isProduk
					,COALESCE(D.nama_diskon,'') AS nama_diskon
				FROM tb_d_diskon AS A
				LEFT JOIN 
				(
					SELECT A.*, COALESCE(B.kode_satuan,'') AS kode_satuan, COALESCE(B.nama_satuan,'') AS nama_satuan 
					FROM tb_satuan_konversi AS A 
					LEFT JOIN tb_satuan AS B
					ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk AND A.kode_satuan = B.kode_satuan
				LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN tb_h_diskon AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_diskon = D.id_h_diskon
				".$cari."
			";
			
            //$query = $this->db->get_where('tb_h_penjualan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            $query = $this->db->query($strquery);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
        }
		
		function list_hari_diskon($id_h_diskon,$kode_kantor)
		{
			$query = 
			"
				SELECT A.hari,COALESCE(B.id_h_diskon,'') AS id_h_diskon,COALESCE(B.kode_kantor,'') AS kode_kantor
					,CASE
						WHEN B.id_h_diskon IS NULL THEN
							0
						ELSE
							1
						END AS AKTIF
					,ORDERBY
				FROM
				(
					
					SELECT 'Monday' AS hari, 1 AS ORDERBY
					UNION ALL
					SELECT 'Tuesday' AS hari, 2 AS ORDERBY
					UNION ALL
					SELECT 'Wednesday' AS hari, 3 AS ORDERBY
					UNION ALL
					SELECT 'Thursday' AS hari, 4 AS ORDERBY
					UNION ALL
					SELECT 'Friday' AS hari, 5 AS ORDERBY
					UNION ALL
					SELECT 'Saturday' AS hari, 6 AS ORDERBY
					UNION ALL
					SELECT 'Sunday' AS hari, 7 AS ORDERBY
					
				) AS A
				LEFT JOIN tb_hari_diskon AS B ON A.hari = B.hari AND B.kode_kantor = '".$kode_kantor."' AND B.id_h_diskon = '".$id_h_diskon."'
				ORDER BY ORDERBY ASC
				;
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
		
		function cek_hari_diskon($cari)
		{
			$query = 
			"
				SELECT * FROM tb_hari_diskon ".$cari."
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
		
		function simpan_hari($id_h_diskon,$hari,$kode_kantor)
		{
			$strquery = 
			"
				INSERT INTO tb_hari_diskon (id_h_diskon, hari, kode_kantor) VALUES ('".$id_h_diskon."','".$hari."','".$kode_kantor."');
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_hari($id_h_diskon,$hari,$kode_kantor)
		{
			$strquery = 
			"
				DELETE FROM tb_hari_diskon WHERE id_h_diskon = '".$id_h_diskon."' AND hari = '".$hari."' AND kode_kantor = '".$kode_kantor."';
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_d_penjualan_dari_d_diskon_by_produk($id_h_penjualan, $id_h_diskon,$kode_kantor,$user)
		{
			$strquery = 
			"
				INSERT INTO tb_d_penjualan
				(
					id_d_penjualan
					,id_h_penjualan	
					,id_d_penerimaan
					,id_produk
					,id_h_diskon
					,id_d_diskon
					,id_kat_produk
					,jumlah
					,status_konversi
					,konversi
					,satuan_jual
					,jumlah_konversi
					,diskon
					,optr_diskon
					,besar_diskon_ori
					,harga
					,harga_konversi
					,harga_ori
					,harga_dasar_ori
					,stock
					,ket_d_penjualan
					,aturan_minum
					,isProduk
					,isReady
					,ket_ready
					,isTerima
					,id_ass
					,nama_ass
					,tgl_ins
					,tgl_updt
					,user_updt
					,user_ins
					,kode_kantor
				)
				SELECT 
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_penjualan
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
								COALESCE(MAX(CAST(RIGHT(id_d_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_penjualan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					)
					,'".$id_h_penjualan."'
					,''
					,A.id_produk
					,A.id_h_diskon
					,A.id_d_diskon
					,A.id_kat_produk
					,A.banyaknya
					,COALESCE(A.status_konversi,'*')
					,COALESCE(A.konversi,1)
					,COALESCE(B.kode_satuan,'')
					,CASE WHEN konversi = '%' THEN
						A.banyaknya / A.konversi 
					ELSE
						A.banyaknya * A.konversi
					END
					,'0'
					,'%'
					,'0'
					,A.nominal
					,CASE WHEN konversi = '%' THEN
						A.nominal * A.konversi 
					ELSE
						A.nominal / A.konversi
					END
					,A.nominal
					,COALESCE(B.harga_jual,0)
					,0
					,ket_d_diskon
					,''
					,COALESCE(C.isProduk,'PRODUK')
					,0
					,''
					,0
					,''
					,''
					,NOW()
					,NOW()
					,'".$user."'
					,'".$user."'
					,'".$kode_kantor."'
				FROM tb_d_diskon AS A
				LEFT JOIN 
				(
					SELECT A.*, COALESCE(B.kode_satuan,'') AS kode_satuan, COALESCE(B.nama_satuan,'') AS nama_satuan 
					FROM tb_satuan_konversi AS A 
					LEFT JOIN tb_satuan AS B
					ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk AND A.kode_satuan = B.kode_satuan
				LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN tb_h_diskon AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_diskon = D.id_h_diskon
				WHERE A.id_h_diskon = '".$id_h_diskon."' AND A.kode_kantor = '".$kode_kantor."';
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function list_h_diskon_paket($kode_kantor,$id_kat_costumer)
		{
			$query = 
			"
				SELECT * FROM tb_h_diskon WHERE kode_kantor = '".$kode_kantor."' AND optr_diskon = 'Paket' AND (id_kat_costumer = '".$id_kat_costumer."' OR id_kat_costumer = '') AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai) ;
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
	
	}
?>
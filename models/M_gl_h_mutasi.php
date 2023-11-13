<?php
	class M_gl_h_mutasi extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_d_mutasi_query($cari,$order_by)
		{
			$query = "
				SELECT
					A.*
					,COALESCE(B.kode_produk,'') AS kode_produk
					,COALESCE(B.nama_produk,'') AS nama_produk
				FROM tb_d_mutasi AS A
				LEFT JOIN tb_produk AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
				
				".$cari."
				".$order_by."
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
		
		function list_lap_h_mutasi($cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT 
					A.* 
					,COALESCE(B.nama_kantor,'') AS nama_kantor_mutasi
					,COALESCE(B.tlp,'') AS tlp_kantor_mutasi
					,COALESCE(B.alamat,'') AS alamat_kantor_mutasi
					
				FROM tb_h_mutasi AS A 
				LEFT JOIN tb_kantor AS B ON A.kode_kantor_mutasi = B.kode_kantor
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
		
		function count_lap_h_mutasi($cari)
		{
			$query = 
			"
				SELECT COUNT(A.id_h_penjualan) AS JUMLAH
				FROM tb_h_mutasi AS A 
				LEFT JOIN tb_kantor AS B ON A.kode_kantor_mutasi = B.kode_kantor
				".$cari."
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
		
		
		function list_lap_detail_mutasi($cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT 
					A.*
					,COALESCE(B.kode_kantor_mutasi,'') AS kode_kantor_mutasi
					,COALESCE(B.no_faktur,'') AS no_faktur
					,COALESCE(DATE(B.tgl_h_penjualan),'') AS tgl_h_penjualan
					,COALESCE(B.ket_penjualan,'') AS ket_penjualan
					,COALESCE(B.status_penjualan,'') AS status_penjualan
					,COALESCE(B.sts_penjualan,'') AS sts_penjualan
					,COALESCE(C.nama_kantor,'') AS nama_kantor_mutasi
					,COALESCE(C.tlp,'') AS tlp_kantor_mutasi
					,COALESCE(C.alamat,'') AS alamat_kantor_mutasi
					,COALESCE(D.kode_produk,'') AS kode_produk
					,COALESCE(D.nama_produk,'') AS nama_produk
					,COALESCE(D.isProduk,'') AS isProduk
					
					,COALESCE(E.id_h_penjualan,'') AS id_h_penjualan_terima
					,COALESCE(E.no_faktur,'') AS no_faktur_penyanding
					,COALESCE(E.tgl_ins,'') AS waktu_terima
					,COALESCE(E.ket_penjualan,'') AS ket_penyanding
					,COALESCE(F.nama_karyawan,'') AS nama_karyawan_penyanding
					
				FROM tb_d_mutasi AS A
				INNER JOIN tb_h_mutasi AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_kantor AS C ON B.kode_kantor_mutasi = C.kode_kantor
				LEFT JOIN tb_produk AS D ON A.id_produk = D.id_produk AND A.kode_kantor = D.kode_kantor
				
				LEFT JOIN tb_h_mutasi AS E ON B.kode_kantor = E.kode_kantor_mutasi AND B.no_faktur = E.no_faktur_penjualan AND E.status_penjualan = 'MUTASI-IN'
				LEFT JOIN tb_karyawan AS F ON A.kode_kantor = F.kode_kantor AND E.user_updt = F.id_karyawan
				
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
		
		function list_lap_detail_mutasi_for_us($cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT 
					A.*
					,COALESCE(B.kode_kantor_mutasi,'') AS kode_kantor_mutasi
					,COALESCE(B.no_faktur,'') AS no_faktur
					,COALESCE(DATE(B.tgl_h_penjualan),'') AS tgl_h_penjualan
					,COALESCE(B.ket_penjualan,'') AS ket_penjualan
					,COALESCE(B.status_penjualan,'') AS status_penjualan
					,COALESCE(C.nama_kantor,'') AS nama_kantor_mutasi
					,COALESCE(C.tlp,'') AS tlp_kantor_mutasi
					,COALESCE(C.alamat,'') AS alamat_kantor_mutasi
					,COALESCE(D.kode_produk,'') AS kode_produk
					,COALESCE(D.nama_produk,'') AS nama_produk
					,COALESCE(D.isProduk,'') AS isProduk
					,COALESCE(E.no_faktur,'') AS no_faktur_penyanding
					,COALESCE(E.ket_penjualan,'') AS ket_penyanding
					
				FROM tb_d_mutasi AS A
				INNER JOIN tb_h_mutasi AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_kantor AS C ON B.kode_kantor = C.kode_kantor
				LEFT JOIN tb_produk AS D ON A.id_produk = D.id_produk AND A.kode_kantor = D.kode_kantor
				
				-- LEFT JOIN tb_h_mutasi AS E ON B.kode_kantor_mutasi = E.kode_kantor AND B.no_faktur = E.no_faktur_penjualan AND E.status_penjualan = 'MUTASI-IN'
				
				LEFT JOIN tb_h_mutasi AS E ON E.kode_kantor = B.kode_kantor_mutasi AND B.no_faktur = E.no_faktur_penjualan AND E.kode_kantor_mutasi = B.kode_kantor AND E.status_penjualan = 'MUTASI-IN'
				
				LEFT JOIN tb_karyawan AS F ON A.kode_kantor = F.kode_kantor AND E.user_updt = F.id_karyawan
				
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
		
		
		function list_h_penjualan($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT A.*, DATE(A.tgl_h_penjualan) AS DT_PENJUALAN
				FROM tb_h_penjualan AS A
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
		
		function list_h_mutasi($cari,$order_by,$limit,$offset)
		{
			/*
			$query =
			"
				SELECT A.*, DATE(A.tgl_h_penjualan) AS DT_PENJUALAN
				FROM tb_h_mutasi AS A
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
			";
			*/
			
			$query =
			"
				SELECT
					A.*
					,DATE(A.tgl_h_penjualan) AS DT_PENJUALAN
					,COALESCE(B.nama_kantor,'') AS nama_kantor
					,COALESCE(B.tlp,'') AS tlp_kantor
					,COALESCE(B.alamat,'') AS alamat_kantor
				FROM tb_h_mutasi AS A
				LEFT JOIN tb_kantor AS B ON A.kode_kantor_mutasi = B.kode_kantor

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
	
		function get_id_h_penjualan_mutasi($kode_kantor)
		{
			
			$query =
			"
				SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_penjualan
				From
				(
					SELECT CONCAT(Y,M,D) AS FRMTGL,Y,M,D
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
						COALESCE(MAX(CAST(RIGHT(id_h_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_h_mutasi
						WHERE DATE(tgl_ins) = DATE(NOW())
						AND kode_kantor = '".$kode_kantor."'
					) AS A
				) AS AA
				;
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
		
		function get_no_faktur_mutasi($jenis,$kode_kantor)
		{
			if($jenis == 'MUTASI-IN')
			{
				$jenis_fix = 'IN';
			}
			elseif($jenis == 'MUTASI-OUT')
			{
				$jenis_fix = 'OUT';
			}
			else
			{
				$jenis_fix = 'SO';
			}
			$query =
			"
				SELECT CONCAT(FRMTGL,'/".$kode_kantor."','/".$jenis_fix."/',ORD) AS NO_FAKTUR
				From
				(
					SELECT CONCAT(Y,M,D) AS FRMTGL,Y,M,D
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
						COALESCE(MAX(CAST(RIGHT(no_faktur,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_h_mutasi
						WHERE DATE(tgl_ins) = DATE(NOW())
						AND status_penjualan = '".$jenis."'
						AND kode_kantor = '".$kode_kantor."'
					) AS A
				) AS AA
				;
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
	
		function list_d_mutasi_produk($cari,$order_by,$limit,$offset)
		{
			$query =
				"
					SELECT * FROM tb_d_mutasi AS A
					LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk 
					AND A.kode_kantor = B.kode_kantor ".$cari." ".$order_by." 
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
		
		
		function get_h_mutasi_by_query($cari)
		{
			$query = "SELECT * FROM tb_h_mutasi ".$cari."";
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
		
		function get_d_mutasi_by_query($cari)
		{
			$query = "SELECT * FROM tb_d_mutasi ".$cari."";
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
		
		function simpan_h_mutasi
		(
			$id_h_penjualan,
			$id_h_pemesanan,
			$id_h_retur,
			$id_gudang_dari,
			$id_gudang_ke,
			$id_costumer,
			$id_karyawan,
			$kode_kantor_mutasi,
			$no_faktur,
			$no_faktur_penjualan,
			$biaya,
			$nominal_retur,
			$bayar,
			$isTunai,
			$tgl_pengajuan,
			$tgl_h_penjualan,
			$tgl_tempo,
			$status_penjualan,
			$ket_penjualan,
			$type_h_penjualan,
			$acc_mutasi,
			$user_updt,
			$kode_kantor,
			$sts_penjualan

		)
		{
			$strquery = "
				INSERT INTO tb_h_mutasi
				(
					id_h_penjualan,
					id_h_pemesanan,
					id_h_retur,
					id_gudang_dari,
					id_gudang_ke,
					id_costumer,
					id_karyawan,
					kode_kantor_mutasi,
					no_faktur,
					no_faktur_penjualan,
					biaya,
					nominal_retur,
					bayar,
					isTunai,
					tgl_pengajuan,
					tgl_h_penjualan,
					tgl_tempo,
					status_penjualan,
					ket_penjualan,
					type_h_penjualan,
					acc_mutasi,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor,
					sts_penjualan
				)
				VALUES
				(
					
					'".$id_h_penjualan."',
					'".$id_h_pemesanan."',
					'".$id_h_retur."',
					'".$id_gudang_dari."',
					'".$id_gudang_ke."',
					'".$id_costumer."',
					'".$id_karyawan."',
					'".$kode_kantor_mutasi."',
					'".$no_faktur."',
					'".$no_faktur_penjualan."',
					'".$biaya."',
					'".$nominal_retur."',
					'".$bayar."',
					'".$isTunai."',
					'".$tgl_pengajuan."',
					'".$tgl_h_penjualan."',
					'".$tgl_tempo."',
					'".$status_penjualan."',
					'".$ket_penjualan."',
					'".$type_h_penjualan."',
					'".$acc_mutasi."',
					NOW(),
					NOW(),
					'".$user_updt."',
					'".$kode_kantor."',
					'".$sts_penjualan."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function simpan_d_mutasi
		(
			$id_h_penjualan,
			$id_d_penerimaan,
			$id_produk,
			$jumlah_req,
			$jumlah,
			$status_konversi,
			$konversi,
			$satuan_jual,
			$jumlah_konversi,
			$harga,
			$harga_konversi,
			$harga_ori,
			$ket_d_penjualan,
			$user_updt,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_d_mutasi
				(
					id_d_penjualan,
					id_h_penjualan,
					id_d_penerimaan,
					id_produk,
					jumlah_req,
					jumlah,
					status_konversi,
					konversi,
					satuan_jual,
					jumlah_konversi,
					harga,
					harga_konversi,
					harga_ori,
					ket_d_penjualan,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor
				)
				VALUES
				(
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
								From tb_d_mutasi
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_penjualan."',
					'".$id_d_penerimaan."',
					'".$id_produk."',
					'".$jumlah_req."',
					'".$jumlah."',
					'".$status_konversi."',
					'".$konversi."',
					'".$satuan_jual."',
					'".$jumlah_konversi."',
					'".$harga."',
					'".$harga_konversi."',
					'".$harga_ori."',
					'".$ket_d_penjualan."',
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
	
		function ubah_d_mutasi
		(
			$id_h_penjualan,
			$id_produk,
			$jumlah_req,
			$jumlah,
			$status_konversi,
			$konversi,
			$satuan_jual,
			$jumlah_konversi,
			$harga,
			$harga_konversi,
			$harga_ori,
			$ket_d_penjualan,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_d_mutasi SET
						jumlah_req = '".$jumlah_req."',
						jumlah = '".$jumlah."',
						status_konversi = '".$status_konversi."',
						konversi = '".$konversi."',
						satuan_jual = '".$satuan_jual."',
						jumlah_konversi = '".$jumlah_konversi."',
						harga = '".$harga."',
						harga_konversi = '".$harga_konversi."',
						harga_ori = '".$harga_ori."',
						ket_d_penjualan = '".$ket_d_penjualan."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
						WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."' AND id_produk = '".$id_produk."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function ubah_h_mutasi
		(
			$id_h_penjualan,
			$id_h_pemesanan,
			$id_h_retur,
			$id_gudang_dari,
			$id_gudang_ke,
			$id_costumer,
			$id_karyawan,
			$kode_kantor_mutasi,
			$no_faktur,
			$no_faktur_penjualan,
			$biaya,
			$nominal_retur,
			$bayar,
			$isTunai,
			$tgl_pengajuan,
			$tgl_h_penjualan,
			$tgl_tempo,
			$status_penjualan,
			$ket_penjualan,
			$type_h_penjualan,
			$acc_mutasi,
			$user_updt,
			$kode_kantor,
			$sts_penjualan,
			$forTindakan

		)
		{
			$strquery = "
					UPDATE tb_h_mutasi SET
					
						id_h_pemesanan = '".$id_h_pemesanan."',
						id_h_retur = '".$id_h_retur."',
						id_gudang_dari = '".$id_gudang_dari."',
						id_gudang_ke = '".$id_gudang_ke."',
						id_costumer = '".$id_costumer."',
						id_karyawan = '".$id_karyawan."',
						kode_kantor_mutasi = '".$kode_kantor_mutasi."',
						no_faktur = '".$no_faktur."',
						no_faktur_penjualan = '".$no_faktur_penjualan."',
						biaya = '".$biaya."',
						nominal_retur = '".$nominal_retur."',
						bayar = '".$bayar."',
						isTunai = '".$isTunai."',
						tgl_pengajuan = '".$tgl_pengajuan."',
						tgl_h_penjualan = '".$tgl_h_penjualan."',
						tgl_tempo = '".$tgl_tempo."',
						status_penjualan = '".$status_penjualan."',
						ket_penjualan = '".$ket_penjualan."',
						type_h_penjualan = '".$type_h_penjualan."',
						acc_mutasi = '".$acc_mutasi."',
						forTindakan = '".$forTindakan."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."',
						sts_penjualan = '".$sts_penjualan."'

						WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function hapus_h_mutasi($cari)
		{
			$strquery = "DELETE FROM tb_h_mutasi ".$cari."";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_d_mutasi($cari)
		{
			$strquery = "DELETE FROM tb_d_mutasi ".$cari."";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_d_mutasi_in_masal($id_h_penjualan,$kode_kantor)
		{
			$strquery = "CALL PROC_INSERT_ALL_D_MUTASI_IN_2('".$id_h_penjualan."','".$kode_kantor."');";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_d_mutasi_out_masal($id_h_penjualan,$kode_kantor)
		{
			$strquery = "CALL PROC_INSERT_ALL_D_MUTASI_OUT_2('".$id_h_penjualan."','".$kode_kantor."');";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_d_mutasi_dari_req_by_procedure($kode_kantor,$id_h_penjualan,$id_h_penjualan_from_source,$kode_cabang_pemesan)
		{
			/*HAPUS JABATAN*/
				$strquery = "
					
CALL SP_LOOP_PO_CABANG_TO_D_MUTASI('".$kode_cabang_pemesan."','".$id_h_penjualan_from_source."','".$kode_kantor."','".$id_h_penjualan."','".$this->session->userdata('ses_is_karyawan')."');
			";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	}
?>
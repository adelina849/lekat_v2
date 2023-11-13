<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_gl_kantor extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
	
	function list_kantor($cari,$limit,$offset)
	{
		
		$query = "SELECT * FROM tb_kantor ".$cari." ORDER BY tgl_ins DESC LIMIT ".$offset.",".$limit;
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
	
	function count_kantor_limit($cari)
	{
		$query = $this->db->query("SELECT COUNT(kode_kantor) AS JUMLAH FROM tb_kantor ".$cari);
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
		$kode_kantor,
		$SITU,
		$SIUP,
		$nama_kantor,
		$pemilik,
		$kota,
		$alamat,
		$tlp,
		$sejarah,
		$ket_kantor,
		$isViewClient,
		$isDefault,
		$isInventory,
		$img_kantor,
		$url_img,
		$sts_kantor,
		$isModePst,
		$user_updt,
		$id_alamat,
		$LOK_LATI,
		$LOK_LONGI

	)
	{
		// SIMPAN DATA KANTOR
			$strquery = "
				INSERT INTO tb_kantor
				(
					id_kantor,
					kode_kantor,
					SITU,
					SIUP,
					nama_kantor,
					pemilik,
					kota,
					alamat,
					tlp,
					sejarah,
					ket_kantor,
					isViewClient,
					isDefault,
					isInventory,
					img_kantor,
					url_img,
					sts_kantor,
					isModePst,
					tgl_ins,
					tgl_updt,
					user_updt,
					id_alamat,
					LOK_LATI,
					LOK_LONGI
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_kantor
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
								COALESCE(MAX(CAST(RIGHT(id_kantor,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kantor
								WHERE DATE(tgl_ins) = DATE(NOW())
								-- AND kode_kantor = ''
							) AS A
						) AS AA
					),
					
					'".$kode_kantor."',
					'".$SITU."',
					'".$SIUP."',
					'".$nama_kantor."',
					'".$pemilik."',
					'".$kota."',
					'".$alamat."',
					'".$tlp."',
					'".$sejarah."',
					'".$ket_kantor."',
					'".$isViewClient."',
					'".$isDefault."',
					'".$isInventory."',
					'".$img_kantor."',
					'".$url_img."',
					'".$sts_kantor."',
					'".$isModePst."',
					NOW(),
					NOW(),
					'".$user_updt."',
					'".$id_alamat."',
					'".$LOK_LATI."',
					'".$LOK_LONGI."'

				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		// SIMPAN DATA KANTOR
		
		// SIMPAN DATA JABATAN
			$strquery = "INSERT INTO `tb_jabatan` (`id_jabatan`, `kode_jabatan`, `nama_jabatan`, `ket_jabatan`, `hirarki`, `tgl_insert`, `tgl_update`, `user`, `user_updt`, `kode_kantor`) VALUES ('JBTN202006100001', '', 'Admin Aplikasi', 'Memegang otoritas tertinggi dalam menjalankan aplikasi/system secara menyeluruh', '0', NOW(), NOW(), '0', '0', '".$kode_kantor."');";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		// SIMPAN DATA JABATAN
		
		// SIMPAN DATA KARYAWAN
			$strquery = "INSERT INTO `tb_karyawan` (`id_karyawan`, `id_jabatan`, `no_karyawan`, `nik_karyawan`, `nama_karyawan`, `pnd`, `tlp`, `email`, `tmp_lahir`, `tgl_lahir`, `kelamin`, `sts_nikah`, `avatar`, `avatar_url`, `alamat`, `ket_karyawan`, `isAktif`, `alasan_phk`, `tgl_phk`, `tgl_diterima`, `isDokter`, `lamar_via`, `nilai_ujian`, `ket_hasil_ujian`, `user`, `pass`, `tgl_updt_pass`, `tgl_ins`, `tgl_updt`, `user_ins`, `user_updt`, `kode_kantor`) VALUES
			('KRY2020053100001', 'JBTN202006100001', '2020053100001', '-', 'Admin Aplikasi', '-', '".$tlp."', '', '".$kota."', '2020-05-31', 'LAKI-LAKI', 'LAJANG', '', '', '".$alamat."', '-', '0', '', '0000-00-00', '2020-05-31', '0', '', 0, '', 'admin', 'NzEzNzhkZjdhN2FhYTU4YmVhYWVjMzAyYzk4ZjYzYWM=', NOW(), NOW(), NOW(), '0', '0', '".$kode_kantor."');";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		// SIMPAN DATA KARYAWAN
	}
	
	function edit
	(
		$kode_kantor,
		$SITU,
		$SIUP,
		$nama_kantor,
		$pemilik,
		$kota,
		$alamat,
		$tlp,
		$sejarah,
		$ket_kantor,
		$isViewClient,
		$isDefault,
		$isInventory,
		$img_kantor,
		$url_img,
		$sts_kantor,
		$isModePst,
		$user_updt,
		$id_alamat,
		$LOK_LATI,
		$LOK_LONGI
	)
	{
		$strquery = "
				UPDATE tb_kantor SET
					SITU = '".$SITU."',
					SIUP = '".$SIUP."',
					nama_kantor = '".$nama_kantor."',
					pemilik = '".$pemilik."',
					kota = '".$kota."',
					alamat = '".$alamat."',
					tlp = '".$tlp."',
					sejarah = '".$sejarah."',
					ket_kantor = '".$ket_kantor."',
					isViewClient = '".$isViewClient."',
					isDefault = '".$isDefault."',
					isInventory = '".$isInventory."',
					img_kantor = '".$img_kantor."',
					url_img = '".$url_img."',
					sts_kantor = '".$sts_kantor."',
					isModePst = '".$isModePst."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."',
					id_alamat = '".$id_alamat."',
					LOK_LATI = '".$LOK_LATI."',
					LOK_LONGI = '".$LOK_LONGI."'
				WHERE kode_kantor = '".$kode_kantor."'
				";
				
		/*SIMPAN DAN CATAT QUERY*/
			$this->M_gl_log->simpan_query($strquery);
		/*SIMPAN DAN CATAT QUERY*/
	}
}

/* End of file M_gl_kantor.php */
/* Location: ./application/models/M_gl_kantor.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2019-12-30 15:11:17 */
/* http://harviacode.com */
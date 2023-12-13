<?php
	class M_karyawan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_karyawan_id($id_karyawan)
		{
			//$query = $this->db->get_where('tb_karyawan', array('id_karyawan' => $id_karyawan), $limit, $offset);
			$query = $this->db->get_where('tb_karyawan', array('id_karyawan' => $id_karyawan));
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function list_karyawan_no_akun($cari,$limit,$offset)
		{
			$query = $this->db->query("
										SELECT A.id_karyawan,A.nik_karyawan, A.nama_karyawan,A.pnd,A.tlp,A.email,A.avatar,A.avatar_url,A.alamat,A.ket_karyawan,B.id_jabatan,B.nama_jabatan,C.id_akun,C.user,C.pass,C.pertanyaan1,C.pertanyaan2,C.jawaban1,C.jawaban2 ,COALESCE(D.KEC_NAMA,'') AS KEC_NAMA
										FROM tb_karyawan AS A
										LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan
										LEFT JOIN tb_akun AS C ON A.id_karyawan = C.id_karyawan
										LEFT JOIN tb_kec AS D ON A.KEC_ID = D.KEC_ID
										WHERE C.user IS NULL ".$cari." ORDER BY nama_karyawan ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_karyawan_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("
										SELECT A.*
											,COALESCE(B.nama_jabatan,'') AS nama_jabatan  
											,COALESCE(C.KEC_NAMA,'') AS KEC_NAMA
											,COALESCE(D.user,'') AS user
											
											,COALESCE(E.img_url,'') AS img_url
											,COALESCE(E.img_file,'') AS img_file
											
										FROM tb_karyawan AS A
										LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan
										LEFT JOIN tb_kec AS C ON A.KEC_ID = C.KEC_ID
										LEFT JOIN tb_akun AS D ON A.id_karyawan = D.id_karyawan
										
										LEFT JOIN
										(
											SELECT 
												id
												,group_by
												,img_url
												,MAX(img_file) AS img_file
											FROM tb_images
											GROUP BY
												id
												,group_by
												,img_url
										) AS E ON A.id_karyawan = E.id AND E.group_by = 'karyawan_kec'
										
										".$cari." ORDER BY A.tgl_ins DESC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_karyawan_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.id_karyawan) AS JUMLAH 
										FROM tb_karyawan AS A
										LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan
										LEFT JOIN tb_kec AS C ON A.KEC_ID = C.KEC_ID
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
		
		function simpan
		(
			$id_jabatan
			,$no_karyawan
			,$nik_karyawan
			,$nama_karyawan
			,$pnd
			,$tlp
			,$email
			,$avatar
			,$avatar_url
			,$alamat
			,$status_kantor
			,$KEC_ID
			,$keterangan
			,$kode_kantor
			,$user_updt
			
			,$jenis_kelamin
			,$tempat_lahir
			,$tgl_lahir
			,$nip
			,$pangkat_gol
			,$tmt_gol_ruang
			,$jabatan
			,$unit_kerja
			,$status_kepeg
			,$kelompok_jabatan
		)
		{
			//Tidak ditambah tgl_ins dan tgl_updt karena sudah current stamp di databasenya
			$data = array
			(
				'id_jabatan' => $id_jabatan,
				'no_karyawan' => $no_karyawan,
				'nik_karyawan' => $nik_karyawan,
				'nama_karyawan' => $nama_karyawan,
				'pnd' => $pnd,
				'tlp' => $tlp,
				'email' => $email,
				'avatar' => $avatar,
				'avatar_url' => $avatar_url,
				'alamat' => $alamat,
				'status_kantor' => $status_kantor,
				'KEC_ID' => $KEC_ID,
				'ket_karyawan' => $keterangan,
				'kode_kantor' => $kode_kantor,
				'user_updt' => $user_updt
			   
				,'jenis_kelamin' => $jenis_kelamin
				,'tempat_lahir' => $tempat_lahir
				,'tgl_lahir' => $tgl_lahir
				,'nip' => $nip
				,'pangkat_gol' => $pangkat_gol
				,'tmt_gol_ruang' => $tmt_gol_ruang
				,'jabatan' => $jabatan
				,'unit_kerja' => $unit_kerja
				,'status_kepeg' => $status_kepeg
				,'kelompok_jabatan' => $kelompok_jabatan
			   
			);

			$this->db->insert('tb_karyawan', $data); 
		}
		
		function edit_saja(
			$id_karyawan
			,$id_jabatan
			,$no_karyawan
			,$nik_karyawan
			,$nama_karyawan
			,$pnd
			,$tlp
			,$email
			,$alamat
			,$status_kantor
			,$KEC_ID
			,$keterangan
			,$user_updt
			
			,$jenis_kelamin
			,$tempat_lahir
			,$tgl_lahir
			,$nip
			,$pangkat_gol
			,$tmt_gol_ruang
			,$jabatan
			,$unit_kerja
			,$status_kepeg
			,$kelompok_jabatan
		)
		{
			
			$id = date('ymdHis'); 
			$date = date('Y-m-d'); 
			$jam = date('Y-m-d H:i:s'); 
			$data = array
			(
			   'id_jabatan' => $id_jabatan,
			   //'no_karyawan' => $no_karyawan,
			   'nik_karyawan' => $nik_karyawan,
			   'nama_karyawan' => $nama_karyawan,
			   'pnd' => $pnd,
			   'tlp' => $tlp,
			   'email' => $email,
			   'alamat' => $alamat,
			   'status_kantor' => $status_kantor,
			   'KEC_ID' => $KEC_ID,
			   'ket_karyawan' => $keterangan,
			   'tgl_updt' => $jam,
			   'user_updt' => $user_updt
			   
				,'jenis_kelamin' => $jenis_kelamin
				,'tempat_lahir' => $tempat_lahir
				,'tgl_lahir' => $tgl_lahir
				,'nip' => $nip
				,'pangkat_gol' => $pangkat_gol
				,'tmt_gol_ruang' => $tmt_gol_ruang
				,'jabatan' => $jabatan
				,'unit_kerja' => $unit_kerja
				,'status_kepeg' => $status_kepeg
				,'kelompok_jabatan' => $kelompok_jabatan
			   
			);
			
			$this->db->where('id_karyawan', $id_karyawan);
			$this->db->update('tb_karyawan', $data);
			
		}
		
		function edit_with_image
		(
			$id_karyawan
			,$id_jabatan
			,$no_karyawan
			,$nik_karyawan
			,$nama_karyawan
			,$pnd
			,$tlp
			,$email
			,$avatar
			,$avatar_url
			,$alamat
			,$status_kantor
			,$KEC_ID
			,$keterangan
			,$user_updt
		)
		{
			$id = date('ymdHis'); 
			$date = date('Y-m-d'); 
			$jam = date('Y-m-d H:i:s'); 
			$data = array
			(
			   'id_jabatan' => $id_jabatan,
			   //'no_karyawan' => $no_karyawan,
			   'nik_karyawan' => $nik_karyawan,
			   'nama_karyawan' => $nama_karyawan,
			   'pnd' => $pnd,
			   'tlp' => $tlp,
			   'email' => $email,
			   'avatar' => $avatar,
			   'avatar_url' => $avatar_url,
			   'alamat' => $alamat,
			   'status_kantor' => $status_kantor,
			   'KEC_ID' => $KEC_ID,
			   'ket_karyawan' => $keterangan,
			   'tgl_updt' => $jam,
			   'user_updt' => $user_updt
			);
			
			$this->db->where('id_karyawan', $id_karyawan);
			$this->db->update('tb_karyawan', $data);
		}
		
		function edit_no_image
		(
			$id_karyawan
			,$id_jabatan
			,$no_karyawan
			,$nik_karyawan
			,$nama_karyawan
			,$pnd
			,$tlp
			,$email
			,$alamat
			,$status_kantor
			,$KEC_ID
			,$keterangan
			,$user_updt
		)
		{
			$id = date('ymdHis'); 
			$date = date('Y-m-d'); 
			$jam = date('Y-m-d H:i:s'); 
			$data = array
			(
			   'id_jabatan' => $id_jabatan,
			   //'no_karyawan' => $no_karyawan,
			   'nik_karyawan' => $nik_karyawan,
			   'nama_karyawan' => $nama_karyawan,
			   'pnd' => $pnd,
			   'tlp' => $tlp,
			   'email' => $email,
			   'alamat' => $alamat,
			   'status_kantor' => $status_kantor,
			   'KEC_ID' => $KEC_ID,
			   'ket_karyawan' => $keterangan,
			   'tgl_updt' => $jam,
			   'user_updt' => $user_updt
			);
			
			$this->db->where('id_karyawan', $id_karyawan);
			$this->db->update('tb_karyawan', $data);
		}
		
		function hapus($id)
		{
			$this->db->query('DELETE FROM tb_karyawan WHERE id_karyawan = '.$id.'');
		}
		
		function get_karyawan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_karyawan', array($berdasarkan => $cari));
            if($query->num_rows() > 0)
            {
                return $query->num_rows();
            }
            else
            {
                return false;
            }
        }
		
	}
?>
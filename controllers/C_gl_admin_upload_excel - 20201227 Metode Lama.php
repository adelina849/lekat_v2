<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_upload_excel extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_kat_costumer','M_gl_costumer','M_gl_produk','M_gl_supplier','M_gl_kat_supplier','M_gl_satuan','M_gl_satuan_konversi','M_gl_harga_pasien','M_gl_h_mutasi','M_gl_hpp_jasa','M_gl_d_assets','M_gl_pst_produk'));
		$this->load->library(array('PHPExcel','PHPExcel/IOFactory'));
		
		
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$msgbox_title = " Pengaturan Upload Data Pasien Via Excel ";
				
				$data = array('page_content'=>'gl_admin_upload_excel_pasien','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function test_tampilkan_array()
	{
		//$query = $this->db->query('SELECT name FROM grade');
		$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		$query = $this->M_gl_kat_costumer->list_kat_costumer_limit($cari,99999,0);
		$data = array();
		foreach ($query->result_array() as $row):                                                     
					$data[] = $row['nama_kat_costumer'];
		endforeach; 
		Echo '<pre>';
			Print_r($data);
		echo '</pre>' ;
		
		echo'<br/><br/><br/>'.$data[2];
		
		$cari = array_search("SHOPEE", $data);
		
		if(!empty($cari))
		{
			echo "</br></br>Hasil pencarian di index : ".$cari;
		}
	}
	
	function test_tampilkan_array_by_key()
	{
		//$query = $this->db->query('SELECT name FROM grade');
		$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		$query = $this->M_gl_kat_costumer->list_kat_costumer_limit($cari,99999,0);
		$data = array();
		foreach ($query->result_array() as $row):                                                     
					$data[$row['nama_kat_costumer']] = $row['id_kat_costumer'];
		endforeach; 
		Echo '<pre>';
			Print_r($data);
		echo '</pre>' ;
		
		echo'<br/><br/><br/>'.$data['SHOPEE'];
		
		/*
		$cari = array_search("SHOPEE", $data);
		if(!empty($cari))
		{
			echo "</br></br>Hasil pencarian di index : ".$cari;
		}
		*/
		
		if(!empty($data['SHOPEE']))
		{
			echo "</br></br>Hasil pencarian di index : ".$data['SHOPEE'];
		}
		
	}
	
	function test_upload_excel()
	//function proses_excel_pasien()
	{
		$fileName = $this->input->post('file', TRUE);

		  $config['upload_path'] = './upload/'; 
		  $config['file_name'] = $fileName;
		  $config['allowed_types'] = 'xls|xlsx|csv|ods|ots';
		  $config['max_size'] = 10000;

		  $this->load->library('upload', $config);
		  $this->upload->initialize($config); 
		  
		  if (!$this->upload->do_upload('file')) {
		   $error = array('error' => $this->upload->display_errors());
		   $this->session->set_flashdata('msg','Ada kesalah dalam upload'); 
		   redirect('gl-admin-pengaturan-upload-excel'); 
		  } else {
		   $media = $this->upload->data();
		   $inputFileName = 'upload/'.$media['file_name'];
		   
		   try {
			$inputFileType = IOFactory::identify($inputFileName);
			$objReader = IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
		   } catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
		   }

		   $sheet = $objPHPExcel->getSheet(0);
		   /*
		   $highestRow = $sheet->getHighestRow();
		   $highestColumn = $sheet->getHighestColumn();
		   */
		   
		   //$highestRow = $_POST['max_row'];
		   $highestRow = $sheet->getHighestRow();
		   //$highestColumn = 13;
		   $highestColumn = $sheet->getHighestColumn();

		   for ($row = 2; $row <= $highestRow; $row++)
		   {  
			 $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
			   NULL,
			   TRUE,
			   FALSE);
				
				echo $rowData[0][0];
				echo ' | ';
				echo $rowData[0][1];
				echo ' | ';
				echo $row;
				echo '<br/>';
				
				/*
				 $data = array(
				 "No"=> $rowData[0][0],
				 "NamaKaryawan"=> $rowData[0][1],
				 "Alamat"=> $rowData[0][2],
				 "Posisi"=> $rowData[0][3],
				 "Status"=> $rowData[0][4]
				
				);
				*/
			//$this->db->insert("tbimport",$data);
		   } 
			@unlink('upload/'.$media['file_name']); //HAPUS KEMBALI FILE
		   $this->session->set_flashdata('msg','Berhasil upload ...!!'); 
		   //redirect('gl-admin-pengaturan-upload-excel');
		  }  
		  
    
	}

	function proses_excel_pasien()
	{
		//GET DATA KATEGORI COSTUMER & SIMPAN KE ARRAY
			$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
			$query = $this->M_gl_kat_costumer->list_kat_costumer_limit($cari,99999,0);
			if(!empty($query))
			{
				$data_arr_kat_costumer = array();
				foreach ($query->result_array() as $row_kat_costumer):                                                     
							$data_arr_kat_costumer[$row_kat_costumer['nama_kat_costumer']] = $row_kat_costumer['id_kat_costumer'];
				endforeach;
				
				$pesan_pasien = "";
			}	
			else
			{
				$data_arr_kat_costumer['kosong'] = "";
				$pesan_pasien = " Tolong lengkapi data pasien";
			}
		//GET DATA KATEGORI COSTUMER & SIMPAN KE ARRAY
		
		$fileName = $this->input->post('file', TRUE);

		$config['upload_path'] = './upload/'; 
		$config['file_name'] = $fileName;
		$config['allowed_types'] = 'xls|xlsx|csv|ods|ots';
		$config['max_size'] = 10000;

		$this->load->library('upload', $config);
		$this->upload->initialize($config); 

		if (!$this->upload->do_upload('file')) 
		{
			$error = array('error' => $this->upload->display_errors());
			//$this->session->set_flashdata('msg','Ada kesalah dalam upload'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL ! </b> '.$this->upload->display_errors().' ('.$pesan_pasien.')/div>');
			//echo $this->session->flashdata('msg');
			redirect('gl-admin-pengaturan-upload-excel'); 
		} 
		else 
		{
			$media = $this->upload->data();
			$inputFileName = 'upload/'.$media['file_name'];

			try {
			$inputFileType = IOFactory::identify($inputFileName);
			$objReader = IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
			}

			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();
			
			/*
				NB : Row Pertama adalah Judul
				A : NIK				:nik
				B : NO PASIEN		:no_costumer
				C : NAMA PASIEN		:nama_lengkap
				D : Jenis/Kategori	:id_kat_costumer
				E : Jenis Kelamin	:jenis_kelamin
				F : Tempat Lahir	:tmp_lahir
				G : Tgl Lahir		:tgl_lahir
				H : No Tlp			:hp
				I : Email			:email_costumer
				J : Alamat			:alamat_rumah_sekarang
				K : Tgl Registrasi	:tgl_registrasi
				L : Piutang Awal	:piutang_awal
				M : Jenis Kunjungan	:jenis_kunjungan
			*/
			//echo'Before looping';
			for ($row = 2; $row <= $highestRow; $row++)
			{ 
				
				$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
				NULL,
				TRUE,
				FALSE);

				/*
				echo $rowData[0][0];
				echo ' | ';
				echo $rowData[0][1];
				echo '<br/>';
				*/
				
				//MULAI MASUK DB DAN PASTIKAN DATA TIDAK KOSONG
				IF(($rowData[0][0] != "") || (!empty($rowData[0][0])))
				{
					//echo'DATA ADA looping';
					$cek_no_pasien = $this->M_gl_costumer->get_costumer("no_costumer",$rowData[0][1]);
					if(!empty($cek_no_pasien))
					{
						$cek_no_pasien = $cek_no_pasien->row();
						//EDIT
							//if($data_arr_kat_costumer['SHOPEE'])
							//if(!empty($data['SHOPEE']))
							if(!empty($data_arr_kat_costumer[$rowData[0][3]]))
							{
								//echo "</br></br>Hasil pencarian di index : ".$data['SHOPEE'];
								$id_kat_costumer = $data_arr_kat_costumer[$rowData[0][3]];
							}
							else
							{
								$id_kat_costumer = "";
							}
							
							$this->M_gl_costumer->edit
							(	
								$cek_no_pasien->id_costumer,
								$cek_no_pasien->id_karyawan,
								$id_kat_costumer,
								$cek_no_pasien->id_kat_costumer2,
								$cek_no_pasien->tgl_pengajuan,
								$rowData[0][1], //$no_costumer,
								$rowData[0][2], //$nama_lengkap,
								$rowData[0][2], //$panggilan,
								$rowData[0][0], //$nik,
								$rowData[0][0], //$no_ktp,
								$rowData[0][5], //$tmp_lahir,
								$rowData[0][6], //$tgl_lahir,
								$rowData[0][4], //$jenis_kelamin,
								$cek_no_pasien->status,
								$rowData[0][9], //$alamat_rumah_sekarang,
								$rowData[0][7], //$hp,
								$cek_no_pasien->status_rumah,
								$cek_no_pasien->lama_menempati,
								$cek_no_pasien->pendidikan,
								$cek_no_pasien->ibu_kandung,
								$cek_no_pasien->nama_perusahaan,
								$cek_no_pasien->alamat_perusahaan,
								$cek_no_pasien->bidang_usaha,
								$cek_no_pasien->jabatan,
								$cek_no_pasien->penghasilan_perbulan,
								$cek_no_pasien->nama_lengkap_pnd,
								$cek_no_pasien->alamat_rumah_pnd,
								$cek_no_pasien->hp_pnd,
								$cek_no_pasien->pekerjaan,
								$cek_no_pasien->hubungan,
								$cek_no_pasien->username,
								$cek_no_pasien->pass,
								$cek_no_pasien->avatar,
								$cek_no_pasien->avatar_url,
								$rowData[0][11], //$piutang_awal,
								$cek_no_pasien->tgl_piutang_awal,
								$cek_no_pasien->isDefault,
								$cek_no_pasien->point_awal,
								$rowData[0][10], //$tgl_registrasi,
								$rowData[0][12], //$jenis_kunjungan,
								$this->session->userdata('ses_id_karyawan'),
								$this->session->userdata('ses_kode_kantor'),
								$rowData[0][8], //$email_costumer,
								$cek_no_pasien->ket_costumer
							);
						//EDIT
						
						//echo 'EDIT <br/>';
					}
					else
					{
						//SIMPAN
							$id_costumer = $this->M_gl_costumer->get_id_costumer($this->session->userdata('ses_kode_kantor'));
							
							//if($data_arr_kat_costumer['SHOPEE'])
							//if(!empty($data['SHOPEE']))
							/*
							if(!empty($data_arr_kat_costumer[$row['D']]))
							{
								//echo "</br></br>Hasil pencarian di index : ".$data['SHOPEE'];
								$id_kat_costumer = $data_arr_kat_costumer[$row['D']];
							}
							*/
							
							if(!empty($data_arr_kat_costumer[$rowData[0][3]]))
							{
								//echo "</br></br>Hasil pencarian di index : ".$data['SHOPEE'];
								$id_kat_costumer = $data_arr_kat_costumer[$rowData[0][3]];
							}
							else
							{
								$id_kat_costumer = "";
							}
							
							$this->M_gl_costumer->simpan
							(	
								$id_costumer->id_costumer,
								'',//$id_karyawan,
								$id_kat_costumer,
								'',//$id_kat_costumer2,
								'',//$tgl_pengajuan,
								$rowData[0][1], //$no_costumer,
								$rowData[0][2], //$nama_lengkap,
								$rowData[0][2], //$panggilan,
								$rowData[0][0], //$nik,
								$rowData[0][0], //$no_ktp,
								$rowData[0][5], //$tmp_lahir,
								$rowData[0][6], //$tgl_lahir,
								$rowData[0][4], //$jenis_kelamin,
								'', //$status,
								$rowData[0][9], //$alamat_rumah_sekarang,
								$rowData[0][7], //$hp,
								'', //$status_rumah,
								'', //$lama_menempati,
								'', //$pendidikan,
								'', //$ibu_kandung,
								'', //$nama_perusahaan,
								'', //$alamat_perusahaan,
								'', //$bidang_usaha,
								'', //$jabatan,
								'', //$penghasilan_perbulan,
								'', //$nama_lengkap_pnd,
								'', //$alamat_rumah_pnd,
								'', //$hp_pnd,
								'', //$pekerjaan,
								'', //$hubungan,
								'', //$username,
								'', //$pass,
								'', //$avatar,
								'', //$avatar_url,
								$rowData[0][11], //$piutang_awal,
								'', //$tgl_piutang_awal,
								'', //$isDefault,
								'', //$point_awal,
								$rowData[0][10], //$tgl_registrasi,
								$rowData[0][12], //$jenis_kunjungan,
								$this->session->userdata('ses_id_karyawan'),
								$this->session->userdata('ses_kode_kantor'),
								$rowData[0][8], //$email_costumer,
								''//$ket_costumer
							);
						//SIMPAN
						//echo 'SIMPAN <br/>';
					}
					//MULAI MASUK DB

					/*
					$data = array(
					"No"=> $rowData[0][0],
					"NamaKaryawan"=> $rowData[0][1],
					"Alamat"=> $rowData[0][2],
					"Posisi"=> $rowData[0][3],
					"Status"=> $rowData[0][4]

					);
					*/
					//$this->db->insert("tbimport",$data);
				}
			}
			
			/* CATAT AKTIFITAS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'UPDATE',
					'Melakukan upload data pasien melalui file excel',
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS*/
			
			@unlink('upload/'.$media['file_name']); //HAPUS KEMBALI FILE
			//$this->session->set_flashdata('msg','Berhasil upload ...!!'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-success"><b>PROSES IMPORT BERHASIL!</b> Data berhasil diimport ! ('.$pesan_pasien.')</div>');
			//echo $this->session->flashdata('msg');
			redirect('gl-admin-pengaturan-upload-excel');
		}
	}
	
	
	function view_upload_produk_hpp()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$msgbox_title = " Pengaturan Upload Data HPP Produk dan Jasa Via Excel ";
				
				$data = array('page_content'=>'gl_admin_upload_excel_produk_hpp','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function proses_upload_produk_hpp()
	{
		//GET DATA SUPPLIER & SIMPAN KE ARRAY
			$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
			$query = $this->M_gl_supplier->list_hanya_supplier_limit($cari,99999,0);
			/*
			$data_arr_supplier = array();
			foreach ($query->result_array() as $row):                                                     
						$data_arr_supplier[$row['nama_supplier']] = $row['id_supplier'];
			endforeach;
			*/
			
			if(!empty($query))
			{
				$data_arr_supplier = array();
				foreach ($query->result_array() as $row):                                                     
							$data_arr_supplier[$row['nama_supplier']] = $row['id_supplier'];
				endforeach;
				$pesan_supplier = "";
			}
			else
			{
				$data_arr_supplier['kosong'] = "";
				$pesan_supplier = " Tolong lengkapi data supplier";
			}
		//GET DATA SUPPLIER & SIMPAN KE ARRAY
		
		//GET DATA SATUAN & SIMPAN KE ARRAY
			$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
			$query = $this->M_gl_satuan->list_satuan_limit($cari,99999,0);
			/*
			$data_arr_satuan = array();
			foreach ($query->result_array() as $row):                                                     
						$data_arr_satuan[$row['kode_satuan']] = $row['id_satuan'];
			endforeach;
			*/
			
			if(!empty($query))
			{
				$data_arr_satuan = array();
				foreach ($query->result_array() as $row):                                                     
							$data_arr_satuan[$row['kode_satuan']] = $row['id_satuan'];
				endforeach;
				
				$pesan_satuan = "";
			}	
			else
			{
				$data_arr_satuan['kosong'] = "";
				$pesan_satuan = " Tolong lengkapi data satuan produk/jasa";
			}
		//GET DATA SATUAN & SIMPAN KE ARRAY
		
		$fileName = $this->input->post('file', TRUE);

		$config['upload_path'] = './upload/'; 
		$config['file_name'] = $fileName;
		$config['allowed_types'] = 'xls|xlsx|csv|ods|ots';
		$config['max_size'] = 10000;

		$this->load->library('upload', $config);
		$this->upload->initialize($config); 

		if (!$this->upload->do_upload('file')) 
		{
			$error = array('error' => $this->upload->display_errors());
			//$this->session->set_flashdata('msg','Ada kesalah dalam upload'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL!</b> '.$this->upload->display_errors().' ('.$pesan_satuan.' '.$pesan_supplier.')</div>');
			redirect('gl-admin-pengaturan-upload-excel-produk-hpp'); 
		} 
		else 
		{
			$media = $this->upload->data();
			$inputFileName = 'upload/'.$media['file_name'];

			try {
			$inputFileType = IOFactory::identify($inputFileName);
			$objReader = IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
			}

			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();
			
			/*
				NB : Row Pertama adalah Judul
				
				1-0.Kode Produk/Jasa/Consumbale
				2-1.Nama Produk/Jasa/Consumbale
				3-2.Nama Umum
				4-3.Supplier
				5-4.Produsen
				6-5.Minimal Stock
				7-6.Maximal Stock
				8-7.Late Time/Lama Pemesanan
				9-8.Moving (Slow,Middle,fast)
				10-9.Jenis (apakah produk, jasa atau consumbale)
				11-10.HPP JASA (tentukan HPP/Modal jasa/tindakan)
				
				12-11.Kode satuan (1)
				13-12.Konversi satuan (1)
				14-13.HPP satuan (1)
				
				15-14.Kode satuan (2)
				16-15.Konversi satuan (2)
				17-16.HPP satuan (2)
				
				18-17.Kode satuan (3)
				19-18.Konversi satuan (3)
				20-19.HPP satuan (3)

			*/

			for ($row = 2; $row <= $highestRow; $row++)
			{  
				$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
				NULL,
				TRUE,
				FALSE);

				/*
				echo $rowData[0][0];
				echo ' | ';
				echo $rowData[0][1];
				echo '<br/>';
				*/
				
				//MULAI MASUK DB
					//PASTIKAN KODE TIDAK KOSONG
					IF(($rowData[0][0] != "") || (!empty($rowData[0][0])))
					{
						//AMBIL DATA ID SUPPLIER
						if(!empty($data_arr_supplier[$rowData[0][2]]))
						{
							//echo "</br></br>Hasil pencarian di index : ".$data['SHOPEE'];
							$id_supplier = $data_arr_supplier[$rowData[0][2]];
						}
						else
						{
							$id_supplier = "";
						}
						//AMBIL DATA ID SUPPLIER
						
						
						//CEK APAKAH PRODUK ADA
							$cek_produk = $this->M_gl_produk->get_produk('kode_produk',$rowData[0][0]);
							if(!empty($cek_produk))
							{
								$cek_produk = $cek_produk->row();
								//PRODUK ADA, BERARTI EDIT
									$this->M_gl_produk->edit_with_hpp_jasa
									(
										$cek_produk->id_produk,
										$id_supplier,
										$rowData[0][0], //$kode_produk,
										$rowData[0][1], //$nama_produk,
										$rowData[0][2], //$nama_umum,
										$rowData[0][3], //$produksi_oleh,
										$rowData[0][4], //$cek_produk->pencipta,
										$cek_produk->charge,
										$cek_produk->optr_charge,
										$cek_produk->charge_beli,
										$cek_produk->optr_charge_beli,
										$rowData[0][5], //$min_stock,
										$rowData[0][6], //$max_stock,
										$cek_produk->harga_minimum,
										$cek_produk->spek_produk,
										$cek_produk->ket_produk,
										$rowData[0][9], //$isProduk,
										$cek_produk->kat_costumer_fee,
										$cek_produk->optr_kondisi_fee,
										$cek_produk->besar_penjualan_fee,
										$cek_produk->satuan_jual_fee,
										$cek_produk->optr_fee,
										$cek_produk->besar_fee,
										$cek_produk->isSattle,
										$cek_produk->buf_stock,
										$rowData[0][7], //$late_time,
										$rowData[0][8], //$jenis_moving,
										$rowData[0][10], //$hpp,
										$this->session->userdata('ses_id_karyawan'),
										$this->session->userdata('ses_kode_kantor')
									);
								//PRODUK ADA, BERARTI EDIT
								
								//INPUT KONVERSI DAN HPP 1, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
									if((($rowData[0][11] != "") || (!empty($rowData[0][11]))) && (($rowData[0][12] != "") || (!empty($rowData[0][12])) || ($rowData[0][12] != "0")))
									{
										//DAPATKAN ID_SATUAN
											if(!empty($data_arr_satuan[$rowData[0][11]]))
											{
												$id_satuan_1 = $data_arr_satuan[$rowData[0][11]];
												//CEK APAKAH SUDAH ADA SATUAN
													$cari_satuan_konversi_1 = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_satuan = '".$id_satuan_1."'";
													
													$cek_satuan_konversi_1 = $this->M_gl_satuan_konversi->cek_satuan_konversi($cari_satuan_konversi_1);
													if(!empty($cek_satuan_konversi_1))
													{
														$cek_satuan_konversi_1 = $cek_satuan_konversi_1->row();
														//KARENA DITEMUKAN JADI EDIT
															$this->M_gl_satuan_konversi->edit
															(
																$cek_satuan_konversi_1->id_satuan_konversi,
																$rowData[0][12], //$konversi,
																$rowData[0][13], //$harga_jual,
																$this->session->userdata('ses_id_karyawan'),
																$this->session->userdata('ses_kode_kantor')
															);
														//KARENA DITEMUKAN JADI EDIT
													}
													else
													{
														//KARENA TIDAK DITEMUKAN JADI SIMPAN
															$this->M_gl_satuan_konversi->simpan
															(
																$cek_produk->id_produk, //$id_produk,
																$id_satuan_1, //$id_satuan,
																'*', //$status,
																$rowData[0][12], //$besar_konversi,
																$rowData[0][13], //$harga_jual,
																'', //$ket_satuan_konversi,
																$this->session->userdata('ses_id_karyawan'),
																$this->session->userdata('ses_kode_kantor')
															);
														//KARENA TIDAK DITEMUKAN JADI SIMPAN
													}
												//CEK APAKAH SUDAH ADA SATUAN
											}
										//DAPATKAN ID_SATUAN
									}
								//INPUT KONVERSI DAN HPP 1, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
								
								//INPUT KONVERSI DAN HPP 2, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
									if((($rowData[0][14] != "") || (!empty($rowData[0][14]))) && (($rowData[0][15] != "") || (!empty($rowData[0][15])) || ($rowData[0][15] != "0")))
									{
										//DAPATKAN ID_SATUAN
											if(!empty($data_arr_satuan[$rowData[0][14]]))
											{
												$id_satuan_2 = $data_arr_satuan[$rowData[0][14]];
												//CEK APAKAH SUDAH ADA SATUAN
													$cari_satuan_konversi_2 = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_satuan = '".$id_satuan_2."'";
													
													$cek_satuan_konversi_2 = $this->M_gl_satuan_konversi->cek_satuan_konversi($cari_satuan_konversi_2);
													if(!empty($cek_satuan_konversi_2))
													{
														$cek_satuan_konversi_2 = $cek_satuan_konversi_2->row();
														//KARENA DITEMUKAN JADI EDIT
															$this->M_gl_satuan_konversi->edit
															(
																$cek_satuan_konversi_2->id_satuan_konversi,
																$rowData[0][15], //$konversi,
																$rowData[0][16], //$harga_jual,
																$this->session->userdata('ses_id_karyawan'),
																$this->session->userdata('ses_kode_kantor')
															);
														//KARENA DITEMUKAN JADI EDIT
													}
													else
													{
														//KARENA TIDAK DITEMUKAN JADI SIMPAN
															$this->M_gl_satuan_konversi->simpan
															(
																$cek_produk->id_produk, //$id_produk,
																$id_satuan_2, //$id_satuan,
																'*', //$status,
																$rowData[0][15], //$besar_konversi,
																$rowData[0][16], //$harga_jual,
																'', //$ket_satuan_konversi,
																$this->session->userdata('ses_id_karyawan'),
																$this->session->userdata('ses_kode_kantor')
															);
														//KARENA TIDAK DITEMUKAN JADI SIMPAN
													}
												//CEK APAKAH SUDAH ADA SATUAN
											}
										//DAPATKAN ID_SATUAN
									}
								//INPUT KONVERSI DAN HPP 2, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
								
								//INPUT KONVERSI DAN HPP 3, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
									if((($rowData[0][17] != "") || (!empty($rowData[0][17]))) && (($rowData[0][18] != "") || (!empty($rowData[0][18])) || ($rowData[0][18] != "0")))
									{
										//DAPATKAN ID_SATUAN
											if(!empty($data_arr_satuan[$rowData[0][17]]))
											{
												$id_satuan_3 = $data_arr_satuan[$rowData[0][17]];
												//CEK APAKAH SUDAH ADA SATUAN
													$cari_satuan_konversi_3 = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_satuan = '".$id_satuan_3."'";
													
													$cek_satuan_konversi_3 = $this->M_gl_satuan_konversi->cek_satuan_konversi($cari_satuan_konversi_3);
													if(!empty($cek_satuan_konversi_2))
													{
														$cek_satuan_konversi_3 = $cek_satuan_konversi_3->row();
														//KARENA DITEMUKAN JADI EDIT
															$this->M_gl_satuan_konversi->edit
															(
																$cek_satuan_konversi_3->id_satuan_konversi,
																$rowData[0][18], //$konversi,
																$rowData[0][19], //$harga_jual,
																$this->session->userdata('ses_id_karyawan'),
																$this->session->userdata('ses_kode_kantor')
															);
														//KARENA DITEMUKAN JADI EDIT
													}
													else
													{
														//KARENA TIDAK DITEMUKAN JADI SIMPAN
															$this->M_gl_satuan_konversi->simpan
															(
																$cek_produk->id_produk, //$id_produk,
																$id_satuan_3, //$id_satuan,
																'*', //$status,
																$rowData[0][18], //$besar_konversi,
																$rowData[0][19], //$harga_jual,
																'', //$ket_satuan_konversi,
																$this->session->userdata('ses_id_karyawan'),
																$this->session->userdata('ses_kode_kantor')
															);
														//KARENA TIDAK DITEMUKAN JADI SIMPAN
													}
												//CEK APAKAH SUDAH ADA SATUAN
											}
										//DAPATKAN ID_SATUAN
									}
								//INPUT KONVERSI DAN HPP 3, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
							}
							else
							{
								
								
								//PRODUK TIDAK ADA, BERARTI SIMPAN
									$this->M_gl_produk->simpan
									(
										$id_supplier,
										$rowData[0][0], //$kode_produk,
										$rowData[0][1], //$nama_produk,
										$rowData[0][2], //$nama_umum,
										$rowData[0][3], //$produksi_oleh,
										$rowData[0][4], //$pencipta,
										'', //$charge,
										'', //$optr_charge,
										'', //$charge_beli,
										'', //$optr_charge_beli,
										$rowData[0][5], //$min_stock,
										$rowData[0][6], //$max_stock,
										'', //$harga_minimum,
										'', //$spek_produk,
										'', //$ket_produk,
										'', //$isNotActive,
										'', //$isNotRetur,
										'', //$stock,
										'', //$dtstock,
										$rowData[0][9], //$isProduk,
										'', //$kat_costumer_fee,
										'', //$optr_kondisi_fee,
										'', //$besar_penjualan_fee,
										'', //$satuan_jual_fee,
										'', //$optr_fee,
										'', //$besar_fee,
										'', //$isSattle,
										'', //$buf_stock,
										$rowData[0][7], //$late_time,
										$rowData[0][8], //$jenis_moving,
										$rowData[0][10], //$hpp,
										$this->session->userdata('ses_id_karyawan'),
										$this->session->userdata('ses_kode_kantor')
									);
								//PRODUK TIDAK ADA, BERARTI SIMPAN
								
								//GET ID PRODUK
									$cek_produk = $this->M_gl_produk->get_produk('kode_produk',$rowData[0][0]);
									if(!empty($cek_produk))
									{
										$cek_produk = $cek_produk->row();
										//INPUT KONVERSI DAN HPP 1, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
											if((($rowData[0][11] != "") || (!empty($rowData[0][11]))) && (($rowData[0][12] != "") || (!empty($rowData[0][12])) || ($rowData[0][12] != "0")))
											{
												//DAPATKAN ID_SATUAN
													if(!empty($data_arr_satuan[$rowData[0][11]]))
													{
														$id_satuan_1 = $data_arr_satuan[$rowData[0][11]];
														//CEK APAKAH SUDAH ADA SATUAN
															$cari_satuan_konversi_1 = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_satuan = '".$id_satuan_1."'";
															
															$cek_satuan_konversi_1 = $this->M_gl_satuan_konversi->cek_satuan_konversi($cari_satuan_konversi_1);
															if(!empty($cek_satuan_konversi_1))
															{
																$cek_satuan_konversi_1 = $cek_satuan_konversi_1->row();
																//KARENA DITEMUKAN JADI EDIT
																	$this->M_gl_satuan_konversi->edit
																	(
																		$cek_satuan_konversi_1->id_satuan_konversi,
																		$rowData[0][12], //$konversi,
																		$rowData[0][13], //$harga_jual,
																		$this->session->userdata('ses_id_karyawan'),
																		$this->session->userdata('ses_kode_kantor')
																	);
																//KARENA DITEMUKAN JADI EDIT
															}
															else
															{
																//KARENA TIDAK DITEMUKAN JADI SIMPAN
																	$this->M_gl_satuan_konversi->simpan
																	(
																		$cek_produk->id_produk, //$id_produk,
																		$id_satuan_1, //$id_satuan,
																		'*', //$status,
																		$rowData[0][12], //$besar_konversi,
																		$rowData[0][13], //$harga_jual,
																		'', //$ket_satuan_konversi,
																		$this->session->userdata('ses_id_karyawan'),
																		$this->session->userdata('ses_kode_kantor')
																	);
																//KARENA TIDAK DITEMUKAN JADI SIMPAN
															}
														//CEK APAKAH SUDAH ADA SATUAN
													}
												//DAPATKAN ID_SATUAN
											}
										//INPUT KONVERSI DAN HPP 1, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
										
										//INPUT KONVERSI DAN HPP 2, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
											if((($rowData[0][14] != "") || (!empty($rowData[0][14]))) && (($rowData[0][15] != "") || (!empty($rowData[0][15])) || ($rowData[0][15] != "0")))
											{
												//DAPATKAN ID_SATUAN
													if(!empty($data_arr_satuan[$rowData[0][14]]))
													{
														$id_satuan_2 = $data_arr_satuan[$rowData[0][14]];
														//CEK APAKAH SUDAH ADA SATUAN
															$cari_satuan_konversi_2 = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_satuan = '".$id_satuan_2."'";
															
															$cek_satuan_konversi_2 = $this->M_gl_satuan_konversi->cek_satuan_konversi($cari_satuan_konversi_2);
															if(!empty($cek_satuan_konversi_2))
															{
																$cek_satuan_konversi_2 = $cek_satuan_konversi_2->row();
																//KARENA DITEMUKAN JADI EDIT
																	$this->M_gl_satuan_konversi->edit
																	(
																		$cek_satuan_konversi_2->id_satuan_konversi,
																		$rowData[0][15], //$konversi,
																		$rowData[0][16], //$harga_jual,
																		$this->session->userdata('ses_id_karyawan'),
																		$this->session->userdata('ses_kode_kantor')
																	);
																//KARENA DITEMUKAN JADI EDIT
															}
															else
															{
																//KARENA TIDAK DITEMUKAN JADI SIMPAN
																	$this->M_gl_satuan_konversi->simpan
																	(
																		$cek_produk->id_produk, //$id_produk,
																		$id_satuan_2, //$id_satuan,
																		'*', //$status,
																		$rowData[0][15], //$besar_konversi,
																		$rowData[0][16], //$harga_jual,
																		'', //$ket_satuan_konversi,
																		$this->session->userdata('ses_id_karyawan'),
																		$this->session->userdata('ses_kode_kantor')
																	);
																//KARENA TIDAK DITEMUKAN JADI SIMPAN
															}
														//CEK APAKAH SUDAH ADA SATUAN
													}
												//DAPATKAN ID_SATUAN
											}
										//INPUT KONVERSI DAN HPP 2, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
										
										//INPUT KONVERSI DAN HPP 3, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
											if((($rowData[0][17] != "") || (!empty($rowData[0][17]))) && (($rowData[0][18] != "") || (!empty($rowData[0][18])) || ($rowData[0][18] != "0")))
											{
												//DAPATKAN ID_SATUAN
													if(!empty($data_arr_satuan[$rowData[0][17]]))
													{
														$id_satuan_3 = $data_arr_satuan[$rowData[0][17]];
														//CEK APAKAH SUDAH ADA SATUAN
															$cari_satuan_konversi_3 = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_satuan = '".$id_satuan_3."'";
															
															$cek_satuan_konversi_3 = $this->M_gl_satuan_konversi->cek_satuan_konversi($cari_satuan_konversi_3);
															if(!empty($cek_satuan_konversi_2))
															{
																$cek_satuan_konversi_3 = $cek_satuan_konversi_3->row();
																//KARENA DITEMUKAN JADI EDIT
																	$this->M_gl_satuan_konversi->edit
																	(
																		$cek_satuan_konversi_3->id_satuan_konversi,
																		$rowData[0][18], //$konversi,
																		$rowData[0][19], //$harga_jual,
																		$this->session->userdata('ses_id_karyawan'),
																		$this->session->userdata('ses_kode_kantor')
																	);
																//KARENA DITEMUKAN JADI EDIT
															}
															else
															{
																//KARENA TIDAK DITEMUKAN JADI SIMPAN
																	$this->M_gl_satuan_konversi->simpan
																	(
																		$cek_produk->id_produk, //$id_produk,
																		$id_satuan_3, //$id_satuan,
																		'*', //$status,
																		$rowData[0][18], //$besar_konversi,
																		$rowData[0][19], //$harga_jual,
																		'', //$ket_satuan_konversi,
																		$this->session->userdata('ses_id_karyawan'),
																		$this->session->userdata('ses_kode_kantor')
																	);
																//KARENA TIDAK DITEMUKAN JADI SIMPAN
															}
														//CEK APAKAH SUDAH ADA SATUAN
													}
												//DAPATKAN ID_SATUAN
											}
										//INPUT KONVERSI DAN HPP 3, PASTIKAN KODE SATUAN DAN KONVERSI TERISI DI EXCEL
									}
								//GET ID PRODUK
							}
						//CEK APAKAH PRODUK ADA
					}
					//PASTIKAN KODE TIDAK KOSONG
				//MULAI MASUK DB

				/*
				$data = array(
				"No"=> $rowData[0][0],
				"NamaKaryawan"=> $rowData[0][1],
				"Alamat"=> $rowData[0][2],
				"Posisi"=> $rowData[0][3],
				"Status"=> $rowData[0][4]

				);
				*/
				//$this->db->insert("tbimport",$data);
			}
			
			/* CATAT AKTIFITAS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'UPDATE',
					'Melakukan upload data produk konversi dan HPP melalui file excel',
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS*/
			
			@unlink('upload/'.$media['file_name']); //HAPUS KEMBALI FILE
			//$this->session->set_flashdata('msg','Berhasil upload ...!!'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-success"><b>PROSES IMPORT BERHASIL!</b> Data berhasil diimport! ('.$pesan_satuan.' '.$pesan_supplier.')</div>');
			redirect('gl-admin-pengaturan-upload-excel-produk-hpp');
		}
	}
	
	function proses_upload_produk_hpp_pusat()
	{
		
		
		
		//SIMPAN YANG BELUM ADA
		$list_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE kode_kantor <> '".$this->session->userdata('ses_kode_kantor')."'");
		if(!empty($list_kantor))
		{
			$list_result = $list_kantor->result();
			
			foreach($list_result as $row)
			{
				//SIMPAN PRODUK YANG BELUM ADA
					$this->M_gl_produk->simpan_produk_all_cabang
					(
						$this->session->userdata('ses_kode_kantor'),$row->kode_kantor
					);
				//SIMPAN PRODUK YANG BELUM ADA
		
				//SIMPAN SATUAN KONVERSI
				$this->M_gl_satuan_konversi->simpan_all_dari_pusat
				(
					$this->session->userdata('ses_kode_kantor'),$row->kode_kantor
				);
				//SIMPAN SATUAN KONVERSI
			}
		}
		//SIMPAN YANG BELUM ADA
		
		//UPDATE SEMUA
			$this->M_gl_satuan_konversi->update_all_dari_pusat($this->session->userdata('ses_kode_kantor'));
		//UPDATE SEMUA
		
		$this->session->set_flashdata('msg', '<div class="alert alert-success"><b>PROSES SINKRONISASI BERHASIL!</b> Berhasil Sinkron! </div>');
		redirect('gl-admin-pengaturan-upload-excel-produk-hpp');
	}

	
	function view_upload_produk_harga_jaul()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$msgbox_title = " Pengaturan Upload Data Harga Jual Produk,Consumbale dan Jasa/tindakan untuk pelanggan Via Excel ";
				
				$data = array('page_content'=>'gl_admin_upload_excel_produk_harga_jual','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function proses_upload_produk_harga_jaul()
	{
		//GET DATA KATEGORI COSTUMER & SIMPAN KE ARRAY
			$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
			$query = $this->M_gl_kat_costumer->list_kat_costumer_limit($cari,99999,0);
			/*
			$data_arr_kat_costumer = array();
			foreach ($query->result_array() as $row):                                                     
						$data_arr_kat_costumer[$row['nama_kat_costumer']] = $row['id_kat_costumer'];
			endforeach;
			*/
			
			if(!empty($query))
			{
				$data_arr_kat_costumer = array();
				foreach ($query->result_array() as $row):                                                     
							$data_arr_kat_costumer[$row['nama_kat_costumer']] = $row['id_kat_costumer'];
				endforeach;
				
				$pesan_kat_costumer = "";
			}	
			else
			{
				$data_arr_kat_costumer['kosong'] = "";
				$pesan_kat_costumer = " Tolong lengkapi data kategori pelanggan/pasien";
			}
		//GET DATA KATEGORI COSTUMER & SIMPAN KE ARRAY
		
		//GET DATA SATUAN & SIMPAN KE ARRAY
			$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
			$query = $this->M_gl_satuan->list_satuan_limit($cari,99999,0);
			/*
			$data_arr_satuan = array();
			foreach ($query->result_array() as $row):                                                     
						$data_arr_satuan[$row['kode_satuan']] = $row['id_satuan'];
			endforeach;
			*/
			
			if(!empty($query))
			{
				$data_arr_satuan = array();
				foreach ($query->result_array() as $row):                                                     
							$data_arr_satuan[$row['kode_satuan']] = $row['id_satuan'];
				endforeach;
				
				$pesan_satuan = "";
			}	
			else
			{
				$data_arr_satuan['kosong'] = "";
				$pesan_satuan = " Tolong lengkapi data satuan";
			}
		//GET DATA SATUAN & SIMPAN KE ARRAY
		
		$fileName = $this->input->post('file', TRUE);

		$config['upload_path'] = './upload/'; 
		$config['file_name'] = $fileName;
		$config['allowed_types'] = 'xls|xlsx|csv|ods|ots';
		$config['max_size'] = 10000;

		$this->load->library('upload', $config);
		$this->upload->initialize($config); 

		if (!$this->upload->do_upload('file')) 
		{
			$error = array('error' => $this->upload->display_errors());
			//$this->session->set_flashdata('msg','Ada kesalah dalam upload'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL!</b> '.$this->upload->display_errors().' ('.$pesan_satuan.', '.$pesan_kat_costumer.')</div>');
			redirect('gl-admin-pengaturan-upload-excel-produk-pasien'); 
		} 
		else 
		{
			$media = $this->upload->data();
			$inputFileName = 'upload/'.$media['file_name'];

			try {
			$inputFileType = IOFactory::identify($inputFileName);
			$objReader = IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
			}

			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();
			
			/*
				NB : Row Pertama adalah Judul
				1-0. Kode produk
				2-1. Nama produk
				
				3-2. Nama/Kode Jenis Pelanggan (1)
				4-3. Kode Satuan (1)
				5-4. VIA (1)
				6-5. Harga Jual (1)
				
				7-6. Nama/Kode Jenis Pelanggan (2)
				8-7. Kode Satuan (2)
				9-8. VIA (2)
				10-9. Harga Jual (2)
				
				11-10. Nama/Kode Jenis Pelanggan (3)
				12-11. Kode Satuan (3)
				13-12. VIA (3)
				14-13. Harga Jual (3)
			*/

			for ($row = 2; $row <= $highestRow; $row++)
			{  
				$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
				NULL,
				TRUE,
				FALSE);

				/*
				echo $rowData[0][0];
				echo ' | ';
				echo $rowData[0][1];
				echo '<br/>';
				*/
				
				//MULAI MASUK DB DAN PASTIKAN DATA TIDAK KOSONG
				IF(($rowData[0][0] != "") || (!empty($rowData[0][0])))
				{
					//CEK APAKAH PRODUK ADA
						$cek_produk = $this->M_gl_produk->get_produk('kode_produk',$rowData[0][0]);
						if(!empty($cek_produk))
						{
							$cek_produk = $cek_produk->row();
							//1. PASTIKAN KODE SATUAN DAN PASIEN TERISI DI EXCEL
								if((($rowData[0][2] != "") || (!empty($rowData[0][2]))) && (($rowData[0][3] != "") || (!empty($rowData[0][3])) || ($rowData[0][3] != "0")))
								{
									//DAPATKAN ID PELANGGAN
									if(!empty($data_arr_kat_costumer[$rowData[0][2]]))
									{
										$id_kat_costumer_1 = $data_arr_kat_costumer[$rowData[0][2]];
									}
									else
									{
										$id_kat_costumer_1 = "";
									}
									
									//DAPATKAN ID PELANGGAN
									
									//DAPATKAN ID SATUAN
									if(!empty($data_arr_satuan[$rowData[0][3]]))
									{
										$id_satuan_1 = $data_arr_satuan[$rowData[0][3]];
									}
									else
									{
										$id_satuan_1 = "";
									}
									//DAPATKAN ID SATUAN
									
									//MULAI PROSES SIMPAN KE DB
									if(($id_kat_costumer_1 <> "") && ($id_satuan_1 <> ""))
									{
										//CEK APAKAH SUDAH ADA DI DB
										$cari_harga_costumer_1 = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_satuan = '".$id_satuan_1."' AND id_costumer = '".$id_kat_costumer_1."' AND media = '".$rowData[0][4]."'";
										
										$cek_harga_costumer = $this->M_gl_harga_pasien->cek_harga_satuan_costumer($cari_harga_costumer_1);
										if(!empty($cek_harga_costumer))
										{
											$cek_harga_costumer = $cek_harga_costumer->row();
											//EDIT
												$this->M_gl_harga_pasien->edit
												(
													$cek_harga_costumer->id_phsc,
													$rowData[0][5], //$harga,
													$rowData[0][4], //$media,
													$this->session->userdata('ses_id_karyawan'),
													$this->session->userdata('ses_kode_kantor')
												);
											//EDIT
										}
										else
										{
											//SIMPAN
												$this->M_gl_harga_pasien->simpan
												(
													$cek_produk->id_produk,
													$id_satuan_1,
													$id_kat_costumer_1,
													$rowData[0][5], //$harga,
													0, //$besar_prsn,
													'*', //$optr_prsn,
													'', //$ket,
													$rowData[0][4], //$media,
													$this->session->userdata('ses_id_karyawan'),
													$this->session->userdata('ses_kode_kantor')
												);
											//SIMPAN
										}
										//CEK APAKAH SUDAH ADA DI DB
									}
									//MULAI PROSES SIMPAN KE DB
								}
							//1. PASTIKAN KODE SATUAN DAN PASIEN TERISI DI EXCEL
							
							//2. PASTIKAN KODE SATUAN DAN PASIEN TERISI DI EXCEL
								if((($rowData[0][6] != "") || (!empty($rowData[0][6]))) && (($rowData[0][7] != "") || (!empty($rowData[0][7])) || ($rowData[0][7] != "0")))
								{
									//DAPATKAN ID PELANGGAN
									if(!empty($data_arr_kat_costumer[$rowData[0][6]]))
									{
										$id_kat_costumer_2 = $data_arr_kat_costumer[$rowData[0][6]];
									}
									else
									{
										$id_kat_costumer_2 = "";
									}
									
									//DAPATKAN ID PELANGGAN
									
									//DAPATKAN ID SATUAN
									if(!empty($data_arr_satuan[$rowData[0][7]]))
									{
										$id_satuan_2 = $data_arr_satuan[$rowData[0][7]];
									}
									else
									{
										$id_satuan_2 = "";
									}
									//DAPATKAN ID SATUAN
									
									//MULAI PROSES SIMPAN KE DB
									if(($id_kat_costumer_2 <> "") && ($id_satuan_2 <> ""))
									{
										//CEK APAKAH SUDAH ADA DI DB
										$cari_harga_costumer_2 = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_satuan = '".$id_satuan_2."' AND id_costumer = '".$id_kat_costumer_2."' AND media = '".$rowData[0][8]."'";
										
										$cek_harga_costumer_2 = $this->M_gl_harga_pasien->cek_harga_satuan_costumer($cari_harga_costumer_2);
										if(!empty($cek_harga_costumer_2))
										{
											$cek_harga_costumer_2 = $cek_harga_costumer_2->row();
											//EDIT
												$this->M_gl_harga_pasien->edit
												(
													$cek_harga_costumer_2->id_phsc,
													$rowData[0][9], //$harga,
													$rowData[0][8], //$media,
													$this->session->userdata('ses_id_karyawan'),
													$this->session->userdata('ses_kode_kantor')
												);
											//EDIT
										}
										else
										{
											//SIMPAN
												$this->M_gl_harga_pasien->simpan
												(
													$cek_produk->id_produk,
													$id_satuan_2,
													$id_kat_costumer_2,
													$rowData[0][9], //$harga,
													0, //$besar_prsn,
													'*', //$optr_prsn,
													'', //$ket,
													$rowData[0][8], //$media,
													$this->session->userdata('ses_id_karyawan'),
													$this->session->userdata('ses_kode_kantor')
												);
											//SIMPAN
										}
										//CEK APAKAH SUDAH ADA DI DB
									}
									//MULAI PROSES SIMPAN KE DB
								}
							//2. PASTIKAN KODE SATUAN DAN PASIEN TERISI DI EXCEL
							
							//3. PASTIKAN KODE SATUAN DAN PASIEN TERISI DI EXCEL
								if((($rowData[0][10] != "") || (!empty($rowData[0][10]))) && (($rowData[0][11] != "") || (!empty($rowData[0][11])) || ($rowData[0][11] != "0")))
								{
									//DAPATKAN ID PELANGGAN
									if(!empty($data_arr_kat_costumer[$rowData[0][10]]))
									{
										$id_kat_costumer_3 = $data_arr_kat_costumer[$rowData[0][10]];
									}
									else
									{
										$id_kat_costumer_3 = "";
									}
									
									//DAPATKAN ID PELANGGAN
									
									//DAPATKAN ID SATUAN
									if(!empty($data_arr_satuan[$rowData[0][11]]))
									{
										$id_satuan_3 = $data_arr_satuan[$rowData[0][11]];
									}
									else
									{
										$id_satuan_3 = "";
									}
									//DAPATKAN ID SATUAN
									
									//MULAI PROSES SIMPAN KE DB
									if(($id_kat_costumer_3 <> "") && ($id_satuan_3 <> ""))
									{
										//CEK APAKAH SUDAH ADA DI DB
										$cari_harga_costumer_3 = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_satuan = '".$id_satuan_3."' AND id_costumer = '".$id_kat_costumer_3."' AND media = '".$rowData[0][12]."'";
										
										$cek_harga_costumer_3 = $this->M_gl_harga_pasien->cek_harga_satuan_costumer($cari_harga_costumer_3);
										if(!empty($cek_harga_costumer_3))
										{
											$cek_harga_costumer_3 = $cek_harga_costumer_3->row();
											//EDIT
												$this->M_gl_harga_pasien->edit
												(
													$cek_harga_costumer_3->id_phsc,
													$rowData[0][13], //$harga,
													$rowData[0][12], //$media,
													$this->session->userdata('ses_id_karyawan'),
													$this->session->userdata('ses_kode_kantor')
												);
											//EDIT
										}
										else
										{
											//SIMPAN
												$this->M_gl_harga_pasien->simpan
												(
													$cek_produk->id_produk,
													$id_satuan_3,
													$id_kat_costumer_3,
													$rowData[0][13], //$harga,
													0, //$besar_prsn,
													'*', //$optr_prsn,
													'', //$ket,
													$rowData[0][12], //$media,
													$this->session->userdata('ses_id_karyawan'),
													$this->session->userdata('ses_kode_kantor')
												);
											//SIMPAN
										}
										//CEK APAKAH SUDAH ADA DI DB
									}
									//MULAI PROSES SIMPAN KE DB
								}
							//3. PASTIKAN KODE SATUAN DAN PASIEN TERISI DI EXCEL
						}
					//CEK APAKAH PRODUK ADA
				}
			}
			
			/* CATAT AKTIFITAS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'UPDATE',
					'Melakukan upload data harga jual produk file excel',
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS*/
			
			@unlink('upload/'.$media['file_name']); //HAPUS KEMBALI FILE
			//$this->session->set_flashdata('msg','Berhasil upload ...!!'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-success"><b>PROSES IMPORT BERHASIL!</b> Data berhasil diimport ! ('.$pesan_satuan.', '.$pesan_kat_costumer.')</div>');
			redirect('gl-admin-pengaturan-upload-excel-produk-pasien');
		}
	}
	
	function proses_upload_produk_harga_jaul_pusat()
	{
		//SIMPAN YANG BELUM ADA
		$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
		if(!empty($list_kantor))
		{
			$list_result = $list_kantor->result();
			
			foreach($list_result as $row)
			{
				$this->M_gl_harga_pasien->simpan_all_dari_pusat
				(
					$this->session->userdata('ses_kode_kantor'),$row->kode_kantor
				);
			}
		}
		//SIMPAN YANG BELUM ADA
		
		//UPDATE SEMUA
			$this->M_gl_harga_pasien->update_all_dari_pusat($this->session->userdata('ses_kode_kantor'));
		//UPDATE SEMUA
		
		$this->session->set_flashdata('msg', '<div class="alert alert-success"><b>PROSES SINKRONISASI BERHASIL!</b> Berhasil Sinkron! </div>');
		redirect('gl-admin-pengaturan-upload-excel-produk-pasien');
	}

	function view_upload_supplier()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$msgbox_title = " Pengaturan Upload Data Supplier Beserta Hutang Awal Via Excel ";
				
				$data = array('page_content'=>'gl_admin_upload_excel_supplier','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function proses_upload_supplier()
	{
		//GET DATA KATEGORI SUPPLIER & SIMPAN KE ARRAY
			$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
			$query = $this->M_gl_kat_supplier->list_kat_supplier_limit($cari,99999,0);
			/*
			$data_arr_kat_supplier = array();
			foreach ($query->result_array() as $row):                                                     
						$data_arr_kat_supplier[$row['nama_kat_supplier']] = $row['id_kat_supplier'];
			endforeach;
			*/
			
			if(!empty($query))
			{
				$data_arr_kat_supplier = array();
				foreach ($query->result_array() as $row):                                                     
							$data_arr_kat_supplier[$row['nama_kat_supplier']] = $row['id_kat_supplier'];
				endforeach;
				
				$pesan_kat_supplier = "";
			}	
			else
			{
				$data_arr_kat_supplier['kosong'] = "";
				$pesan_kat_supplier = " Tolong lengkapi data kategori supplier";
			}
		//GET DATA KATEGORI SUPPLIER & SIMPAN KE ARRAY
		
		$fileName = $this->input->post('file', TRUE);

		$config['upload_path'] = './upload/'; 
		$config['file_name'] = $fileName;
		$config['allowed_types'] = 'xls|xlsx|csv|ods|ots';
		$config['max_size'] = 10000;

		$this->load->library('upload', $config);
		$this->upload->initialize($config); 

		if (!$this->upload->do_upload('file')) 
		{
			$error = array('error' => $this->upload->display_errors());
			//$this->session->set_flashdata('msg','Ada kesalah dalam upload'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL!</b> '.$this->upload->display_errors().' ('.$pesan_kat_supplier.')</div>');
			redirect('gl-admin-pengaturan-upload-excel-supplier'); 
		} 
		else 
		{
			$media = $this->upload->data();
			$inputFileName = 'upload/'.$media['file_name'];

			try {
			$inputFileType = IOFactory::identify($inputFileName);
			$objReader = IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
			}

			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();
			
			/*
				NB : Row Pertama adalah Judul
				0. No Supplier
				1. Nama SUPPLIER
				2. Kategori
				3. Jenis
				4. Pemilik
				5. SITU
				6. SIUP
				7. Jenis Usaha
				8. Telpon
				9. Email
				10. Alamat
				11. Budget
				12. Utang Awal
				13. Tgl Utang Awal
			*/

			for ($row = 2; $row <= $highestRow; $row++)
			{  
				$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
				NULL,
				TRUE,
				FALSE);

				/*
				echo $rowData[0][0];
				echo ' | ';
				echo $rowData[0][1];
				echo '<br/>';
				*/
				
				//MULAI MASUK DB DAN PASTIKAN DATA TIDAK KOSONG
				IF(($rowData[0][0] != "") || (!empty($rowData[0][0])))
				{
					$cek_no_supplier = $this->M_gl_supplier->get_supplier("kode_supplier",$rowData[0][0]);
					if(!empty($cek_no_supplier))
					{
						$cek_no_supplier = $cek_no_supplier->row();
						//EDIT
							//if($data_arr_kat_costumer['SHOPEE'])
							//if(!empty($data['SHOPEE']))
							if(!empty($data_arr_kat_supplier[$rowData[0][2]]))
							{
								//echo "</br></br>Hasil pencarian di index : ".$data['SHOPEE'];
								$id_kat_supplier = $data_arr_kat_supplier[$rowData[0][2]];
							}
							else
							{
								$id_kat_supplier = "";
							}
							
							$this->M_gl_supplier->edit
							(	
								$cek_no_supplier->id_supplier,
								$id_kat_supplier,
								$rowData[0][0], //$kode_supplier,
								$rowData[0][0], //$no_supplier,
								$rowData[0][1], //$nama_supplier,
								$rowData[0][4], //$pemilik_supplier,
								$rowData[0][5], //$situ,
								$rowData[0][6], //$siup,
								$rowData[0][7], //$bidang,
								$cek_no_supplier->ket_supplier,
								$cek_no_supplier->avatar,
								$cek_no_supplier->avatar_url,
								$rowData[0][9], //$email,
								$rowData[0][8], //$tlp,
								$rowData[0][10], //$alamat,
								$rowData[0][11], //$limit_budget,
								$cek_no_supplier->allow_budget,
								$rowData[0][14], //$bank,
								$rowData[0][15], //$norek,
								$rowData[0][12], //$hutang_awal,
								$cek_no_supplier->type_supplier,
								$rowData[0][13], //$tgl_hutang_awal,
								$this->session->userdata('ses_id_karyawan'),
								$this->session->userdata('ses_kode_kantor')
							);
						//EDIT
					}
					else
					{
						//SIMPAN
							
							if(!empty($data_arr_kat_supplier[$rowData[0][2]]))
							{
								//echo "</br></br>Hasil pencarian di index : ".$data['SHOPEE'];
								$id_kat_supplier = $data_arr_kat_supplier[$rowData[0][2]];
							}
							else
							{
								$id_kat_supplier = "";
							}
							
							$this->M_gl_supplier->simpan
							(	
								$id_kat_supplier,
								$rowData[0][0], //$kode_supplier,
								$rowData[0][0], //$no_supplier,
								$rowData[0][1], //$nama_supplier,
								$rowData[0][4], //$pemilik_supplier,
								$rowData[0][5], //$situ,
								$rowData[0][6], //$siup,
								$rowData[0][7], //$bidang,
								'', //$ket_supplier,
								'', //$avatar,
								'', //$avatar_url,
								$rowData[0][9], //$email,
								$rowData[0][8], //$tlp,
								$rowData[0][10], //$alamat,
								$rowData[0][11], //$limit_budget,
								'1', //$allow_budget,
								$rowData[0][14], //$bank,
								$rowData[0][15], //$norek,
								$rowData[0][12], //$hutang_awal,
								'', //$type_supplier,
								$rowData[0][13], //$tgl_hutang_awal,
								$this->session->userdata('ses_id_karyawan'),
								$this->session->userdata('ses_kode_kantor')
							
							);
						//SIMPAN
					}
					//MULAI MASUK DB

					/*
					$data = array(
					"No"=> $rowData[0][0],
					"NamaKaryawan"=> $rowData[0][1],
					"Alamat"=> $rowData[0][2],
					"Posisi"=> $rowData[0][3],
					"Status"=> $rowData[0][4]

					);
					*/
					//$this->db->insert("tbimport",$data);
				}
			}
			
			/* CATAT AKTIFITAS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'UPDATE',
					'Melakukan upload data supplier melalui file excel',
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS*/
			
			@unlink('upload/'.$media['file_name']); //HAPUS KEMBALI FILE
			//$this->session->set_flashdata('msg','Berhasil upload ...!!'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-success"><b>PROSES IMPORT BERHASIL!</b> Data berhasil diimport ! ('.$pesan_kat_supplier.')</div>');
			redirect('gl-admin-pengaturan-upload-excel-supplier');
		}
	}

	function view_upload_stock_produk()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.kode_produk LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_produk LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$list_upload_stock_produk = $this->M_gl_satuan_konversi->list_stock_upload($cari);
				$msgbox_title = " Pengaturan Upload Data Penyesuaian Stock Produk Via Excel ";
				
				$data = array('page_content'=>'gl_admin_upload_excel_stock','msgbox_title' => $msgbox_title,'list_upload_stock_produk' => $list_upload_stock_produk);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function proses_upload_stock_produk()
	{
		$fileName = $this->input->post('file', TRUE);

		$config['upload_path'] = './upload/'; 
		$config['file_name'] = $fileName;
		$config['allowed_types'] = 'xls|xlsx|csv|ods|ots';
		$config['max_size'] = 10000;

		$this->load->library('upload', $config);
		$this->upload->initialize($config); 

		if (!$this->upload->do_upload('file')) 
		{
			$error = array('error' => $this->upload->display_errors());
			//$this->session->set_flashdata('msg','Ada kesalah dalam upload'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL!</b> '.$this->upload->display_errors().'</div>');
			redirect('gl-admin-pengaturan-upload-excel-stock'); 
		} 
		else 
		{
			$media = $this->upload->data();
			$inputFileName = 'upload/'.$media['file_name'];

			try {
			$inputFileType = IOFactory::identify($inputFileName);
			$objReader = IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
			}

			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();
			
			/*
				NB : Row Pertama adalah Judul
				0. Kode Produk/Barcode
				1. Nama Produk
				2. nama umum
				3. satuan
				4. stock
				
			*/
			
			/*HAPUS DATA PENAMPUNGAN*/
				$cari_so = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$cari_ims = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$this->M_gl_satuan_konversi->hapus_tb_penampungan($cari_so,$cari_ims);
			/*HAPUS DATA PENAMPUNGAN*/
			
			/*PANGGIL DAN ISI KEMBALI TMP IMS*/
				$this->M_gl_satuan_konversi->simpan_tmp_ims($this->session->userdata('ses_kode_kantor'));
			/*PANGGIL DAN ISI KEMBALI TMP IMS*/
			
			for ($row = 2; $row <= $highestRow; $row++)
			{  
				$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
				NULL,
				TRUE,
				FALSE);

				/*
				echo $rowData[0][0];
				echo ' | ';
				echo $rowData[0][1];
				echo '<br/>';
				*/
				
				//PASTIKAN KODE TIDAK KOSONG
				IF(($rowData[0][0] != "") || (!empty($rowData[0][0])))
				{
					//CEK APAKAH PRODUK ADA
					$cek_produk = $this->M_gl_produk->get_produk('kode_produk',$rowData[0][0]);
					if(!empty($cek_produk))
					{
						$cek_produk = $cek_produk->row();
						$this->M_gl_satuan_konversi->simpan_tmp_so
						(
							$rowData[0][0], //$kode_produk,
							$rowData[0][1], //$nama_produk,
							$rowData[0][2], //$nama_supplier,
							$rowData[0][3], //$satuan_deft,
							0, //$stock_gudang,
							0, //$stock_toko,
							$rowData[0][4], //$stock_all,
							$this->session->userdata('ses_kode_kantor') //$kode_kantor

						);
					}
				}
					
				
			}
			
			/* CATAT AKTIFITAS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'UPDATE',
					'Melakukan upload data stock produk melalui file excel',
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS*/
			
			@unlink('upload/'.$media['file_name']); //HAPUS KEMBALI FILE
			//$this->session->set_flashdata('msg','Berhasil upload ...!!'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-success"><b>PROSES IMPORT BERHASIL!</b> Data berhasil diimport!</div>');
			redirect('gl-admin-pengaturan-upload-excel-stock');
		}
	}

	function proses_2_upload_stock_produk()
	{
		/*DAPATKAN ID H MUTASI IN*/
			$id_h_penjualan_in = $this->M_gl_h_mutasi->get_id_h_penjualan_mutasi($this->session->userdata('ses_kode_kantor'));
			$id_h_penjualan_in = $id_h_penjualan_in->id_h_penjualan;
		/*DAPATKAN ID H MUTASI IN*/
		
		/*DAPATKAN NO FAKTUR H MUTASI IN*/
			$no_faktur_in = $this->M_gl_h_mutasi->get_no_faktur_mutasi('MUTASI-IN',$this->session->userdata('ses_kode_kantor'));
			$no_faktur_in = $no_faktur_in->NO_FAKTUR;
		/*DAPATKAN NO FAKTUR H MUTASI IN*/
		
		/*SIMPAN H MUTASI IN*/
			$this->M_gl_h_mutasi->simpan_h_mutasi
												(
													$id_h_penjualan_in, //id_h_penjualan,
													'', //$id_h_pemesanan,
													'', //$id_h_retur,
													'', //$id_gudang_dari,
													'', //$id_gudang_ke,
													'', //$id_costumer,
													'', //$id_karyawan,
													$this->session->userdata('ses_kode_kantor'), //$kode_kantor_mutasi,
													$no_faktur_in,
													'', //$no_faktur_penjualan,
													'', //$biaya,
													'', //$nominal_retur,
													'', //$bayar,
													'TUNAI', //$isTunai,
													date('Y-m-d'), //$tgl_pengajuan,
													date('Y-m-d'), //$tgl_h_penjualan,
													date('Y-m-d'), //$tgl_tempo,
													'MUTASI-IN', //$status_penjualan,
													'ADJUSTMENT STOCK', //$ket_penjualan,
													'', //$type_h_penjualan,
													0, //$acc_mutasi,
													$this->session->userdata('ses_id_karyawan'),
													$this->session->userdata('ses_kode_kantor'),
													'SELESAI' //$sts_penjualan

												);
		/*SIMPAN H MUTASI IN*/
		
		/*SIMPAN D_MASAL IN*/
			$this->M_gl_h_mutasi->simpan_d_mutasi_in_masal($id_h_penjualan_in,$this->session->userdata('ses_kode_kantor'));
		/*SIMPAN D_MASAL IN*/
		
		/* ================================================================================================= */
		
		/*DAPATKAN ID H MUTASI OUT*/
			$id_h_penjualan_out = $this->M_gl_h_mutasi->get_id_h_penjualan_mutasi($this->session->userdata('ses_kode_kantor'));
			$id_h_penjualan_out = $id_h_penjualan_out->id_h_penjualan;
		/*DAPATKAN ID H MUTASI OUT*/
		
		/*DAPATKAN NO FAKTUR H MUTASI OUT*/
			$no_faktur_out = $this->M_gl_h_mutasi->get_no_faktur_mutasi('MUTASI-OUT',$this->session->userdata('ses_kode_kantor'));
			$no_faktur_out = $no_faktur_out->NO_FAKTUR;
		/*DAPATKAN NO FAKTUR H MUTASI OUT*/
		
		/*SIMPAN H MUTASI OUT*/
			$this->M_gl_h_mutasi->simpan_h_mutasi
												(
													$id_h_penjualan_out, //id_h_penjualan,
													'', //$id_h_pemesanan,
													'', //$id_h_retur,
													'', //$id_gudang_dari,
													'', //$id_gudang_ke,
													'', //$id_costumer,
													'', //$id_karyawan,
													$this->session->userdata('ses_kode_kantor'), //$kode_kantor_mutasi,
													$no_faktur_out,
													'', //$no_faktur_penjualan,
													'', //$biaya,
													'', //$nominal_retur,
													'', //$bayar,
													'TUNAI', //$isTunai,
													date('Y-m-d'), //$tgl_pengajuan,
													date('Y-m-d'), //$tgl_h_penjualan,
													date('Y-m-d'), //$tgl_tempo,
													'MUTASI-OUT', //$status_penjualan,
													'ADJUSTMENT STOCK', //$ket_penjualan,
													'', //$type_h_penjualan,
													0, //$acc_mutasi,
													$this->session->userdata('ses_id_karyawan'),
													$this->session->userdata('ses_kode_kantor'),
													'SELESAI' //$sts_penjualan

												);
		/*SIMPAN H MUTASI OUT*/
		
		/*SIMPAN D_MASAL OUT*/
			$this->M_gl_h_mutasi->simpan_d_mutasi_out_masal($id_h_penjualan_out,$this->session->userdata('ses_kode_kantor'));
		/*SIMPAN D_MASAL OUT*/
		
		
		/*HAPUS DATA PENAMPUNGAN*/
			$cari_so = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
			$cari_ims = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
			$this->M_gl_satuan_konversi->hapus_tb_penampungan($cari_so,$cari_ims);
		/*HAPUS DATA PENAMPUNGAN*/
		$this->session->set_flashdata('msg', '<div class="alert alert-success"><b>PROSES ADJUSTMEN STOCK BERHASIL!</b> Data berhasil diimport!</div>');
		redirect('gl-admin-pengaturan-upload-excel-stock');
	}
	
	function view_upload_kebutuhan_jasa()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$msgbox_title = " Pengaturan Upload Data Kebutuhan Jasa/Tindakan ";
				
				$data = array('page_content'=>'gl_admin_upload_excel_kebutuhan_jasa','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function proses_upload_kebutuhan_jasa()
	{
		$fileName = $this->input->post('file', TRUE);

		$config['upload_path'] = './upload/'; 
		$config['file_name'] = $fileName;
		$config['allowed_types'] = 'xls|xlsx|csv|ods|ots';
		$config['max_size'] = 10000;

		$this->load->library('upload', $config);
		$this->upload->initialize($config); 

		if (!$this->upload->do_upload('file')) 
		{
			$error = array('error' => $this->upload->display_errors());
			//$this->session->set_flashdata('msg','Ada kesalah dalam upload'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-danger"><b>PROSES IMPORT GAGAL!</b> '.$this->upload->display_errors().'</div>');
			redirect('gl-admin-pengaturan-upload-excel-produk-hpp-jasa'); 
		} 
		else 
		{
			$media = $this->upload->data();
			$inputFileName = 'upload/'.$media['file_name'];

			try {
			$inputFileType = IOFactory::identify($inputFileName);
			$objReader = IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			} catch(Exception $e) {
			die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
			}

			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();
			$highestColumn = $sheet->getHighestColumn();
			
			/*
				NB : Row Pertama adalah Judul
				1-0. Kode Tindakan
				2-1. Nama Tindakan
				3-2. Kategori Kebutuhan
				4-3. Kode Assets/Consumable
				5-4. Nama Assets/Consumable
				6-5. Jumlah Kebutuhan
				7-6. SATUAN
				8-7. Harga
				9-8. Keterangan
				
			*/
			
			
			for ($row = 2; $row <= $highestRow; $row++)
			{  
				$rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
				NULL,
				TRUE,
				FALSE);

				/*
				echo $rowData[0][0];
				echo ' | ';
				echo $rowData[0][1];
				echo '<br/>';
				*/
				
				//PASTIKAN KODE TIDAK KOSONG
				IF(($rowData[0][0] != "") || (!empty($rowData[0][0])))
				{
					//CEK APAKAH PRODUK ADA
					$cek_produk = $this->M_gl_produk->get_produk('kode_produk',$rowData[0][0]);
					if(!empty($cek_produk))
					{
						
						$cek_produk = $cek_produk->row();
						//CEK APAKAH ASSETS ATAU CONSUMABLE
						if($rowData[0][2] == "CONSUMABLE")
						{
							//CEK APAKAH ADA KODE ITU DI TABLE PRODUK
							$cari_produk_consumable = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND kode_produk = '".$rowData[0][3]."' AND isProduk = 'CONSUMABLE'";
							//$cek_produk_assets_hpp = $this->M_gl_produk->get_produk('kode_produk',$rowData[0][3]);
							
							$cek_produk_assets_hpp = $this->M_gl_produk->get_produk_cari($cari_produk_consumable);
							if(!empty($cek_produk_assets_hpp))
							{
								$cek_produk_assets_hpp = $cek_produk_assets_hpp->row();
								$id_produk_hpp = $cek_produk_assets_hpp->id_produk;
								$id_assets_hpp = "";
							}
							else
							{
								$id_produk_hpp = "";
								$id_assets_hpp = "";
							}
							//CEK APAKAH ADA KODE ITU DI TABLE PRODUK
							
							$status_hpp = "CONSUMABLE";
						}
						else
						{
							//CEK APAKAH ADA DI TABLE ASSETS
							$cek_produk_assets_hpp = $this->M_gl_d_assets->get_d_assets('kode_assets',$rowData[0][3]);
							if(!empty($cek_produk_assets_hpp))
							{
								$cek_produk_assets_hpp = $cek_produk_assets_hpp->row();
								$id_assets_hpp = $cek_produk_assets_hpp->id_d_assets;
								$id_produk_hpp = "";
							}
							else
							{
								$id_assets_hpp = "";
								$id_produk_hpp = "";
							}
							//CEK APAKAH ADA DI TABLE ASSETS
							
							$status_hpp = "ASSETS";
						}
						//CEK APAKAH ASSETS ATAU CONSUMABLE
						
						//echo $cek_produk->id_produk.' | '.$rowData[0][2].' | '.$id_produk_hpp.'</br>';
						
						//CEK APAKAH SUDAH ADA DI TABLE HPP JASA
							if($status_hpp = "CONSUMABLE")
							{
								$cek_apakah_sudah_input_ke_hpp_jasa = $this->M_gl_hpp_jasa->get_hpp_jasa_cari(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_produk_hpp = '".$id_produk_hpp."'");
							}
							else
							{
								$cek_apakah_sudah_input_ke_hpp_jasa = $this->M_gl_hpp_jasa->get_hpp_jasa_cari(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$cek_produk->id_produk."' AND id_assets = '".$id_assets_hpp."'");
							}
							
							if(!empty($cek_apakah_sudah_input_ke_hpp_jasa))
							{
								$cek_apakah_sudah_input_ke_hpp_jasa = $cek_apakah_sudah_input_ke_hpp_jasa->row();
								$this->M_gl_hpp_jasa->edit
								(
									$cek_apakah_sudah_input_ke_hpp_jasa->id_hpp_jasa,
									$cek_produk->id_produk,
									$id_produk_hpp,
									$id_assets_hpp,
									$rowData[0][5], //$banyaknya,
									$rowData[0][6], //$satuan,
									$rowData[0][7], //$harga,
									$rowData[0][8], //$ket_hpp,
									$this->session->userdata('ses_id_karyawan'),
									$this->session->userdata('ses_kode_kantor') //$kode_kantor
								);
								
								//echo 'EDIT <br/>';
							}
							else
							{
								$this->M_gl_hpp_jasa->simpan
								(
									$cek_produk->id_produk,
									$id_produk_hpp,
									$id_assets_hpp,
									$rowData[0][5], //$banyaknya,
									$rowData[0][6], //$satuan,
									$rowData[0][7], //$harga,
									$rowData[0][8], //$ket_hpp,
									$this->session->userdata('ses_id_karyawan'),
									$this->session->userdata('ses_kode_kantor') //$kode_kantor
								);
								
								///echo 'SIMPAN <br/>';
							}
						//CEK APAKAH SUDAH ADA DI TABLE HPP JASA
						
						//UBAH HPP PRODUK
							$this->M_gl_hpp_jasa->ubah_hpp_jasa($cek_produk->id_produk);
						//UBAH HPP PRODUK
					}
				}
					
				
			}
			
			/* CATAT AKTIFITAS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'UPDATE',
					'Melakukan upload data stock produk melalui file excel',
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS*/
			
			@unlink('upload/'.$media['file_name']); //HAPUS KEMBALI FILE
			//$this->session->set_flashdata('msg','Berhasil upload ...!!'); 
			$this->session->set_flashdata('msg', '<div class="alert alert-success"><b>PROSES IMPORT BERHASIL!</b> Data berhasil diimport!</div>');
			redirect('gl-admin-pengaturan-upload-excel-produk-hpp-jasa');
		}
	}

	function view_excel_contoh_stock()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isProduk <> 'JASA'";
				$order_by = "ORDER BY A.nama_produk ASC";
				$limit = 100000;
				$offset = 0;
				
				$list_produk = $this->M_gl_produk->list_produk_saja($cari,$order_by,$limit,$offset);
				//$msgbox_title = " Pengaturan Upload Data Kebutuhan Jasa/Tindakan ";
				
				$data = array('list_produk' => $list_produk);
				$this->load->view('admin/page/gl_admin_excel_contoh_stock.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function view_excel_contoh_produk_hpp_produk()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isProduk <> 'JASA'";
				$order_by = "ORDER BY A.nama_produk ASC";
				$limit = 100000;
				$offset = 0;
				
				$list_produk = $this->M_gl_produk->list_produk_saja_dengan_hpp_untuk_excel($cari,$order_by,$limit,$offset);
				//$msgbox_title = " Pengaturan Upload Data Kebutuhan Jasa/Tindakan ";
				
				$data = array('list_produk' => $list_produk);
				$this->load->view('admin/page/gl_admin_excel_contoh_produk_hpp.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function view_excel_contoh_produk_hpp_jasa()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isProduk = 'JASA'";
				$order_by = "ORDER BY A.nama_produk ASC";
				$limit = 100000;
				$offset = 0;
				
				$list_produk = $this->M_gl_produk->list_produk_saja_dengan_hpp_untuk_excel($cari,$order_by,$limit,$offset);
				//$msgbox_title = " Pengaturan Upload Data Kebutuhan Jasa/Tindakan ";
				
				$data = array('list_produk' => $list_produk);
				$this->load->view('admin/page/gl_admin_excel_contoh_produk_hpp.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function view_excel_contoh_produk_harga_jual_produk()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isProduk <> 'JASA'";
				$order_by = "ORDER BY A.nama_produk ASC";
				$limit = 100000;
				$offset = 0;
				
				$list_produk = $this->M_gl_produk->list_produk_saja_dengan_harga_jual_costumer_untuk_excel($cari,$order_by,$limit,$offset);
				//$msgbox_title = " Pengaturan Upload Data Kebutuhan Jasa/Tindakan ";
				
				$data = array('list_produk' => $list_produk);
				$this->load->view('admin/page/gl_admin_excel_contoh_harga_jual.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function view_excel_contoh_produk_harga_jual_jasa()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isProduk = 'JASA'";
				$order_by = "ORDER BY A.nama_produk ASC";
				$limit = 100000;
				$offset = 0;
				
				$list_produk = $this->M_gl_produk->list_produk_saja_dengan_harga_jual_costumer_untuk_excel($cari,$order_by,$limit,$offset);
				//$msgbox_title = " Pengaturan Upload Data Kebutuhan Jasa/Tindakan ";
				
				$data = array('list_produk' => $list_produk);
				$this->load->view('admin/page/gl_admin_excel_contoh_harga_jual.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_upload_excel.php */
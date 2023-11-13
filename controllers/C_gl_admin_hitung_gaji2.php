<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class C_gl_admin_hitung_gaji extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('M_gl_hitung_gaji','M_gl_gaji_pokok','M_akun','M_tunjangan_karyawan','M_potongan_karyawan','M_gl_pengaturan'));
        $this->load->library('form_validation');
	       $this->load->library('datatables');
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

            $id_karyawan = $this->uri->segment(2,0);
            $kode_kantor = $this->uri->segment(3,0);
            $periode = $this->uri->segment(4,0);

            if($periode == '') $periode = date('Y-m');

            $tgl_cur = date_create($periode.'-01');
            $tgl_to = $tgl_cur->format('Y-m-23');

            $tgl_conv = date_create($periode.'-01'.' first day of last month');
            $tgl_from = $tgl_conv->format('Y-m-24');

            if($periode == '')
            {
               $periode = date('Y-m');
            }

            $header = $this->M_gl_hitung_gaji->get_karyawan($id_karyawan,$kode_kantor);
            $list_kantor = $this->M_gl_hitung_gaji->list_kantor();

            $list_tunjangan = $this->M_gl_hitung_gaji->list_tunjangan_karyawan($id_karyawan,$kode_kantor,$periode);
            $list_potongan = $this->M_gl_hitung_gaji->list_potongan_karyawan($id_karyawan,$kode_kantor,$periode);
            $summary = $this->M_gl_hitung_gaji->summary_karyawan($id_karyawan,$kode_kantor,$periode,$tgl_from,$tgl_to);
            
            if(empty($summary)) {
                $summary = '';
            }

            $data = array(
                      'page_content'=>'c_gl_hitung_gaji/gl_hitung_gaji',
                      'header'=>$header,
                      'list_tunjangan' => $list_tunjangan,
                      'list_potongan' => $list_potongan,
                      'list_kantor' => $list_kantor,
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'summary' => $summary,
                      'kode_kantor' => $kode_kantor,
                    );
            $this->load->view('pusat/container',$data);
          }
          else
          {
            header('Location: '.base_url().'gl-admin-login');
          }
        }
    }

    public function list_karyawan()
    {
      $cari = $this->input->post('cari');

      $data = $this->M_gl_hitung_gaji->list_karyawan($cari);

      $no=1;
      //echo 'dodol';
      foreach ($data as $row) {
        echo '<tr>';
        echo '<input type="hidden" id="kode_kantor_'.$no.'" value="'.$row->kode_kantor.'" />';
        echo '<td><input type="hidden" id="id_karyawan_'.$no.'" value="'.$row->id_karyawan.'" />'.$no.'</td>';
        echo '<td><input type="hidden" id="no_karyawan_'.$no.'" value="'.$row->no_karyawan.'" />'.$row->no_karyawan.'</td>';
        echo '<td><input type="hidden" id="nama_karyawan_'.$no.'" value="'.$row->nama_karyawan.'" />'.$row->nama_karyawan.'</td>';
        echo '<td><input type="hidden" id="nama_kantor_'.$no.'" value="'.$row->nama_kantor.'" />'.$row->nama_kantor.'</td>';
        echo '<td><button type="button" class="btn btn-info" data-dismiss="modal" onclick="pilihKaryawan('.$no.')">Pilih</button></td>';
        echo '</tr>';
        $no++;
      }
    }

    public function hitung_potongan_absensi()
    {
      $periode = $this->input->post('periode');
      $tgl_to = $this->input->post('tgl_to');

      //echo $periode;

      $tgl_conv = date_create($tgl_to.' first day of last month');

      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'ABSEN');

      // $data = $this->M_gl_hitung_gaji->list_karyawan('');

      // foreach ($data as $row) {
      //   $this->M_gl_hitung_gaji->hitung_potongan_absen($row->id_karyawan,$row->kode_kantor,$tgl_from,$tgl_to,$periode);  
      // }

      $this->M_gl_hitung_gaji->hitung_potongan_absen_v2($tgl_from,$tgl_to,$periode);  

      echo 'ok';
    }

    public function hitung_upselling()
    {
      $periode = $this->input->post('periode');
      $tgl_to = $this->input->post('tgl_to');

      //echo $periode;

      $tgl_conv = date_create($tgl_to.' first day of last month');

      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'UPSELLING');

      $this->M_gl_hitung_gaji->hitung_upselling($tgl_from,$tgl_to,$periode);

      echo 'ok';
      
    }

    public function hitung_uang_makan()
    {
      $periode = $this->input->post('periode');
      $tgl_to = $this->input->post('tgl_to');

      //echo $periode;

      $tgl_conv = date_create($tgl_to.' first day of last month');

      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'UM');

      $this->M_gl_hitung_gaji->hitung_uang_makan($tgl_from,$tgl_to,$periode);

      echo 'ok';
    }

    public function hitung_setahun()
    {
        $periode = $this->input->post('periode');
        $tgl_to = $this->input->post('tgl_to');

        //echo $periode;

        $tgl_conv = date_create($tgl_to.' first day of last month');

        $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

        $this->M_gl_hitung_gaji->delete_d_tampung($periode,'KENAIKAN');

        $this->M_gl_hitung_gaji->hitung_kenaikan_setahun($tgl_from,$tgl_to,$periode);

        echo 'ok';
    }

    public function hitung_potongan_late()
    {
      $periode = $this->input->post('periode');
      //$kode_kantor = $this->input->post('kode_kantor');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'LATE');

      $list_kode_kantor = array('CJR','BDG','JKT','KNG','CPNS','SBY','OLN','MDN');

      for ($i = 0; $i < count($list_kode_kantor); $i++) {
          
        $kode_kantor = $list_kode_kantor[$i];

        $cabang = '0';
        if($kode_kantor == 'CJR') $cabang = '0';
        if($kode_kantor == 'BDG') $cabang = '1';
        if($kode_kantor == 'JKT') $cabang = '2';
        if($kode_kantor == 'KNG') $cabang = '3';
        if($kode_kantor == 'CPNS') $cabang = '4';
        if($kode_kantor == 'SBY') $cabang = '5';
        if($kode_kantor == 'OLN') $cabang = '6';

        //tarik data terlambat per periode
        $data_absen = $this->M_gl_hitung_gaji->tarik_late_cabang($tgl_from,$tgl_to,$cabang,'');
        //print_r($data_absen);

        $data = $this->M_gl_hitung_gaji->list_karyawan('');

        //print_r($data);
        
        
        foreach ($data as $row) {
          $id_karyawan = $row->id_karyawan;
          $id_mesin_empl = $row->id_mesin_empl;
          $jenis_karyawan = $row->isDokter;
          
          

          $list_jam_kerja = $this->M_gl_hitung_gaji->list_jam_kerja($id_karyawan,$tgl_from,$tgl_to,$kode_kantor);

          foreach ($list_jam_kerja as $row2) {

            //print_r($data_absen);
            foreach ($data_absen as $row3) {
              if($row3->tanggal == $row2->tanggal && $row3->id_karyawan == $row2->id_mesin_empl && $row3->kode_kantor == $row2->kode_kantor)
              {
                if($row3->num == '1')
                 {
                   $jam_masuk_aktual = $row3->jam;
                   $jam_masuk = $row2->jam_masuk;

                   $j_aktual = strtotime($row3->tanggal." ".$jam_masuk_aktual);
                   $j_masuk = strtotime($row3->tanggal." ".$jam_masuk);

                   $late = round(($j_aktual - $j_masuk) / 60,2);

                   // if($row3->nama_empl == 'ONOLN2020102300001')
                   // {
                   //   echo 'late : '.$late.'<br>';
                   // }

                   $late_murni = 0;

                   if($late == 10) $late = 9;
                   if($late == 15) $late = 14;
                   if($late == 20) $late = 19;
                   if($late == 25) $late = 24;
                   if($late == 30) $late = 29;
                   if($late == 35) $late = 34;
                   if($late == 40) $late = 39;
                   if($late == 45) $late = 44;
                   if($late == 50) $late = 49;
                   if($late == 55) $late = 54;
                   if($late == 60) $late = 59;
                   if($late == 65) $late = 64;
                   if($late == 70) $late = 69;
                   if($late == 75) $late = 74;
                   if($late == 80) $late = 79;
                   if($late == 85) $late = 84;
                   if($late == 90) $late = 89;
                   if($late == 95) $late = 94;
                   if($late == 100) $late = 99;
                   if($late > 100) $late = 99;


                   if($jenis_karyawan == 'MANAGEMENT')
                   {
                      $late_murni = $late - 10;
                   } else if($jenis_karyawan == 'DOKTER')
                   {
                      $late_murni = $late - 15;
                   } else {
                      $late_murni = $late - 5;
                   }



                   $nilai = floor(($late_murni / 5) +1);

                   if($nilai < 0) $nilai = 0;

                   $nominal = $nilai * 5000;
                   
                   if($id_karyawan == '9')
                   {
                     echo 'telat '.$late;
                     echo '<br>';
                     echo 'nilai'.$nilai;
                     echo '<br>'; 
                   }

                   if($late_murni > 0)
                   { 
                    $cek = $this->M_gl_hitung_gaji->cek_d_tampung($id_karyawan,$periode,'LATE');
                    if(empty($cek))
                    {
                      $post = array(
                          'id_karyawan' => $id_karyawan,
                          'periode' => $periode,
                          'kode_akun' => 'LATE',
                          'nama_akun' => 'Terlambat',
                          'jenis_akun' => 'KREDIT',
                          'keterangan' => '',
                          'nominal' => $nominal,
                          'user_ins' => $this->session->userdata('ses_id_karyawan'),
                          'kode_kantor' => $kode_kantor
                      );

                      $this->M_gl_hitung_gaji->insert_d_tampung_gaji($post);
                    } else {
                      $this->M_gl_hitung_gaji->update_d_tampung($id_karyawan,$periode,'LATE',$nominal);
                    }
                  }
                      
                 }
              }
            }
          }

        }
      }
      //echo 'ok';
    }

    public function hitung_lembur_v2()
    { 
      $periode = $this->input->post('periode');
      //$kode_kantor = $this->input->post('kode_kantor');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'LEMBUR');
      
      $this->M_gl_hitung_gaji->tarik_lembur_v2($tgl_from,$tgl_to,$periode);

      echo 'ok';

    }

    public function detail_ins_lembur()
    {
        $id_karyawan = $this->uri->segment(2,0);
        $tgl_to = $this->uri->segment(3,0);
        $periode = $this->uri->segment(4,0);

        $tgl_conv = date_create($tgl_to.' first day of last month');
        $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02


        $list_data = $this->M_gl_hitung_gaji->detail_ins_lembur($id_karyawan,$tgl_from,$tgl_to);  
        
        $data = array(
                        'page_content'=>'c_gl_hitung_gaji/detail_ins_lembur',
                        'id_karyawan' => $id_karyawan,
                        'periode' => $periode,
                        'tgl_to' => $tgl_to,
                        'data' => $list_data,
                      );
        $this->load->view('pusat/container',$data);
    }

    public function detail_potongan_absen()
    {
        $id_karyawan = $this->uri->segment(2,0);
        $tgl_to = $this->uri->segment(3,0);
        $periode = $this->uri->segment(4,0);

        $tgl_conv = date_create($tgl_to.' first day of last month');
        $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02


        $list_data = $this->M_gl_hitung_gaji->detail_potongan_absen($id_karyawan,$tgl_from,$tgl_to);  
        
        $data = array(
                        'page_content'=>'c_gl_hitung_gaji/detail_potongan_absen',
                        'id_karyawan' => $id_karyawan,
                        'periode' => $periode,
                        'tgl_to' => $tgl_to,
                        'data' => $list_data,
                      );
        $this->load->view('pusat/container',$data);
    }

    public function detail_punishment()
    {
        $id_karyawan = $this->uri->segment(2,0);
        $tgl_to = $this->uri->segment(3,0);
        $periode = $this->uri->segment(4,0);

        $tgl_conv = date_create($tgl_to.' first day of last month');
        $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02


        $list_data = $this->M_gl_hitung_gaji->detail_punishment($id_karyawan,$tgl_from,$tgl_to);  
        
        $data = array(
                        'page_content'=>'c_gl_hitung_gaji/detail_punishment',
                        'id_karyawan' => $id_karyawan,
                        'periode' => $periode,
                        'tgl_to' => $tgl_to,
                        'data' => $list_data,
                      );
        $this->load->view('pusat/container',$data);
    }

    public function hitung_lembur()
    {
      $periode = $this->input->post('periode');
      //$kode_kantor = $this->input->post('kode_kantor');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'LEMBUR');

      $list_kode_kantor = array('CJR','BDG','JKT','KNG','CPNS','SBY','OLN','MDN');

      for ($i = 0; $i < count($list_kode_kantor); $i++) {
          
        $kode_kantor = $list_kode_kantor[$i];

        $cabang = '0';
        if($kode_kantor == 'CJR') $cabang = '0';
        if($kode_kantor == 'BDG') $cabang = '1';
        if($kode_kantor == 'JKT') $cabang = '2';
        if($kode_kantor == 'KNG') $cabang = '3';
        if($kode_kantor == 'CPNS') $cabang = '4';
        if($kode_kantor == 'SBY') $cabang = '5';
        if($kode_kantor == 'OLN') $cabang = '6';

        //tarik data terlambat per periode
        $data_absen = $this->M_gl_hitung_gaji->tarik_lembur($tgl_from,$tgl_to,$cabang,'');
        //print_r($data_absen);

        $data = $this->M_gl_hitung_gaji->list_karyawan('');

        //print_r($data);
        
        
        foreach ($data as $row) {
          $id_karyawan = $row->id_karyawan;
          $id_mesin_empl = $row->id_mesin_empl;
          $jenis_karyawan = $row->nama_jabatan;
          $masa_kerja = $row->masa_kerja;

          $list_jam_kerja = $this->M_gl_hitung_gaji->list_jam_kerja($id_karyawan,$tgl_from,$tgl_to,$kode_kantor);

          foreach ($list_jam_kerja as $row2) {

            //print_r($data_absen);
            foreach ($data_absen as $row3) {
              if($row3->tanggal == $row2->tanggal && $row3->id_karyawan == $row2->id_mesin_empl && $row3->kode_kantor == $row2->kode_kantor)
              {
                if(($row3->num == '2' && $row3->jam >= '18:00') || ($row3->num == '3' && $row3->jam >= '18:00'))
                {
                   $jam_keluar_aktual = $row3->jam;
                   $jam_keluar = $row2->jam_keluar;

                   $j_aktual = strtotime($row3->tanggal." ".$jam_keluar_aktual);
                   $j_keluar = strtotime($row3->tanggal." ".$jam_keluar);

                   //ambil nilai lembur per 30 menit = 1 point;
                   $lembur = floor(($j_aktual - $j_keluar) / 1800);
                   $lembur_murni = 0;

                   //echo ' aktual keluar: '.$jam_keluar_aktual;
                   //echo ' jam keluar: '.$jam_keluar;
                   //echo ' lembur: '.$lembur;
                   $nilai=0;
                   
                   if($lembur > 0 && $jenis_karyawan != 'MANAGEMENT') // && $jenis_karyawan != 'MANAGEMENT'
                   { 
                    if($jenis_karyawan == 'DOKTER' && $masa_kerja < 1)
                    {
                      $nilai = 20000;  //setengah dari 20rb/jam
                    } else if($jenis_karyawan == 'DOKTER' && $masa_kerja >= 1)
                    {
                      $nilai = 40000;  //setengah dari 40rb/jam
                    } else if($jenis_karyawan == 'APOTEKER')
                    {
                      $nilai = 20000;
                    }
                    else if($jenis_karyawan == 'DESAIN GRAFIS' && $masa_kerja < 2 )
                    {
                      $nilai = 10000;
                    } 
                    else if($jenis_karyawan == 'DESAIN GRAFIS' && $masa_kerja >= 2 )
                    {
                      $nilai = 20000;
                    } else {
                      if($masa_kerja < 2 )
                      {
                        $nilai = 10000;
                      } else {
                        $nilai = 20000;
                      }
                    }

                    $nominal = $nilai * $lembur;

                    $cek = $this->M_gl_hitung_gaji->cek_d_tampung($id_karyawan,$periode,'LEMBUR');
                    if(empty($cek))
                    {
                      $post = array(
                          'id_karyawan' => $id_karyawan,
                          'periode' => $periode,
                          'kode_akun' => 'LEMBUR',
                          'nama_akun' => 'Ins Lembur',
                          'jenis_akun' => 'DEBIT',
                          'keterangan' => '',
                          'nominal' => $nominal,
                          'user_ins' => $this->session->userdata('ses_id_karyawan'),
                          'kode_kantor' => $kode_kantor
                      );

                      $this->M_gl_hitung_gaji->insert_d_tampung_gaji($post);
                    } else {
                      $this->M_gl_hitung_gaji->update_d_tampung($id_karyawan,$periode,'LEMBUR',$nominal);
                    }
                  }
                      
                 }
              }
            }
          }

        }
      }
      //echo 'ok';
    }

    public function hitung_hari_libur()
    {
      $periode = $this->input->post('periode');
      //$kode_kantor = $this->input->post('kode_kantor');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      //HAPUS DLU JIKA SUDAH ADA DATA
      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'LIBUR');

      $this->M_gl_hitung_gaji->hitung_hari_libur($tgl_from,$tgl_to,$periode);

      echo 'ok';
    }

    public function hitung_ins_tindakan()
    {
      $periode = $this->input->post('periode');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      //HAPUS DLU JIKA SUDAH ADA DATA
      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'TINDAKAN');

      $this->M_gl_hitung_gaji->hitung_ins_terapis($tgl_from,$tgl_to,$periode);

      $tgl_from = '2021-10-01';//$tgl_conv->format('Y-m-24');
      $tgl_to = '2021-10-20';
      
      $this->M_gl_hitung_gaji->hitung_ins_dokter_tahap1_lama($tgl_from,$tgl_to,$periode);

      $this->M_gl_hitung_gaji->hitung_ins_dokter_tahap1_baru($tgl_from,$tgl_to,$periode);

      $this->M_gl_hitung_gaji->hitung_ins_dokter_tahap2($tgl_from,$tgl_to,$periode);

      echo 'ok';
    }

    public function hitung_konsultasi_pasien_baru()
    {
      $periode = $this->input->post('periode');
      //$kode_kantor = $this->input->post('kode_kantor');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      //HAPUS DLU JIKA SUDAH ADA DATA
      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'KONSUL1');

      $list_dokter = $this->M_gl_hitung_gaji->list_dokter();


      //INSENTIF PASIEN BARU
      foreach ($list_dokter as $row) {
          $this->M_gl_hitung_gaji->hitung_konsul_pasien_baru($row->id_karyawan,$tgl_from,$tgl_to,$periode);
      }

      echo 'ok';
    }

    public function hitung_konsultasi_pasien_lama()
    {
      $periode = $this->input->post('periode');
      //$kode_kantor = $this->input->post('kode_kantor');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      //HAPUS DLU JIKA SUDAH ADA DATA
      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'KONSUL2');

      $list_dokter = $this->M_gl_hitung_gaji->list_dokter();


      //INSENTIF PASIEN BARU
      foreach ($list_dokter as $row) {
          $this->M_gl_hitung_gaji->hitung_konsul_pasien_lama($row->id_karyawan,$tgl_from,$tgl_to,$periode);
      }

      echo 'ok';
    }

    public function hitung_tindakan_dokter()
    {
      $id_karyawan = $this->input->post('id_karyawan');
      $periode = $this->input->post('periode');
      $nominal = $this->input->post('nominal');

      $cek = $this->M_gl_hitung_gaji->cek_d_tampung($id_karyawan,$periode,'TINDAKAN');

      if(empty($cek))
      {
        $post = array(
            'id_karyawan' => $id_karyawan,
            'periode' => $periode,
            'kode_akun' => 'TINDAKAN',
            'nama_akun' => 'Ins Tindakan',
            'jenis_akun' => 'DEBIT',
            'keterangan' => '',
            'nominal' => $nominal,
            'user_ins' => $this->session->userdata('ses_id_karyawan'),
            'kode_kantor' => $this->session->userdata('ses_kode_kantor')
        );

        $this->M_gl_hitung_gaji->insert_d_tampung_gaji($post);
      } else {
        $this->M_gl_hitung_gaji->update_d_tampung2($id_karyawan,$periode,'TINDAKAN',$nominal);
      }

      echo 'ok';
    }

    public function hitung_tindakan_lebih()
    {
      $periode = $this->input->post('periode');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      //HAPUS DLU JIKA SUDAH ADA DATA
      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'LEBIH10');

      $this->M_gl_hitung_gaji->hitung_tindakan_lebih($tgl_from,$tgl_to,$periode);

      echo 'ok';
    }

    public function hitung_produk_lebih()
    {
      $periode = $this->input->post('periode');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      //HAPUS DLU JIKA SUDAH ADA DATA
      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'LEBIH1');

      $this->M_gl_hitung_gaji->hitung_produk_lebih($tgl_from,$tgl_to,$periode);

      echo 'ok';
    }

    public function hitung_punishment()
    {
      $periode = $this->input->post('periode');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      //HAPUS DLU JIKA SUDAH ADA DATA
      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'PUNISH');

      $this->M_gl_hitung_gaji->hitung_punishment($tgl_from,$tgl_to,$periode);

      echo 'ok';
    }

    public function hitung_ins_ass_apoteker()
    {
      $periode = $this->input->post('periode');
      $tgl_to = $this->input->post('tgl_to');
      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      //HAPUS DLU JIKA SUDAH ADA DATA
      $this->M_gl_hitung_gaji->delete_d_tampung($periode,'ASSIST');
      $this->M_gl_hitung_gaji->hitung_ins_resp_apoteker($tgl_from,$tgl_to,$periode);

      $this->M_gl_hitung_gaji->hitung_ins_ass_dokter($tgl_from,$tgl_to,$periode);

      echo 'ok';
    }

    public function detail_ins_ass()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);
      $kode_kantor = $this->uri->segment(5,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $get_karyawan = $this->M_gl_hitung_gaji->get_karyawan($id_karyawan,$kode_kantor);

      if($get_karyawan->nama_jabatan == 'APOTEKER' || $get_karyawan->nama_jabatan == 'ASST. APOTEKER')
      {
        $list_data = $this->M_gl_hitung_gaji->detail_ins_apoteker($id_karyawan,$tgl_from,$tgl_to);
        $page = 'detail_ins_apoteker';
      } else {
        $list_data = $this->M_gl_hitung_gaji->detail_ins_ass($id_karyawan,$tgl_from,$tgl_to);  
        $page = 'detail_ins_ass';
      }
      
      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/'.$page,
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );
        $this->load->view('pusat/container',$data);
    }


    public function update_tunjangan()
    {
      $id = $this->input->post('id_tunjangan');
      $id_karyawan = $this->input->post('id_karyawan');
      $periode = $this->input->post('periode');
      $kode_tunjangan = $this->input->post('kode_tunjangan');
      $nama_tunjangan = $this->input->post('nama_tunjangan');
      $id_tampung = $this->input->post('id_tampung');
      $nominal = $this->input->post('nominal');
      $tgl = date('Y-m-d H:i:s');

      if($id_tampung == '')
      {
        $data = array(
            'id_karyawan' => $id_karyawan,
            'periode' => $periode,
            'kode_akun' => $kode_tunjangan,
            'nama_akun' => $nama_tunjangan,
            'jenis_akun' => 'DEBIT',
            'keterangan' => '',
            'nominal' => $nominal,
            'user_ins' => $this->session->userdata('ses_id_karyawan'),
            'kode_kantor' => $this->session->userdata('ses_kode_kantor')
          );

        $this->M_gl_hitung_gaji->insert_d_tampung_gaji($data);

      } else {
        $this->M_gl_hitung_gaji->update_d_tampung_by_id($id_tampung,$nominal);  
      }

    
      // $data = array(
      //   'nominal' => $this->input->post('nominal',TRUE),
      //   'tgl_updt' => $tgl,
      // );

      // $this->M_tunjangan_karyawan->update($id, $data);
    }

    public function hapus_tunjangan()
    {
      $id = $this->input->post('id_tunjangan');
      $id_tampung = $this->input->post('id_tampung');
      $this->M_tunjangan_karyawan->delete($id);

      if($id_tampung != '')
      {
        $this->M_gl_hitung_gaji->delete_d_tampung_by_id($id_tampung);
      } 
    }

    public function setaktif_tunjangan()
    {
      $id = $this->input->post('id_tunjangan');
      $is_aktif = $this->input->post('is_aktif');

      $tgl = date('Y-m-d H:i:s');

      $data = array(
        'is_aktif' => $is_aktif,
        'tgl_updt' => $tgl,
      );

      $this->M_tunjangan_karyawan->update($id, $data); 
    }

    public function update_potongan()
    {
      $id = $this->input->post('id_potongan');
      $id_tampung = $this->input->post('id_tampung');
      $nominal = $this->input->post('nominal');
      $tgl = date('Y-m-d H:i:s');

      $id_karyawan = $this->input->post('id_karyawan');
      $periode = $this->input->post('periode');
      $kode_potongan = $this->input->post('kode_potongan');
      $nama_potongan = $this->input->post('nama_potongan');

      $this->M_gl_hitung_gaji->update_d_tampung_by_id($id_tampung,$nominal);

      if($id_tampung == '')
      {
        $data = array(
            'id_karyawan' => $id_karyawan,
            'periode' => $periode,
            'kode_akun' => $kode_potongan,
            'nama_akun' => $nama_potongan,
            'jenis_akun' => 'KREDIT',
            'keterangan' => '',
            'nominal' => $nominal,
            'user_ins' => $this->session->userdata('ses_id_karyawan'),
            'kode_kantor' => $this->session->userdata('ses_kode_kantor')
          );

        $this->M_gl_hitung_gaji->insert_d_tampung_gaji($data);

      } else {
        $this->M_gl_hitung_gaji->update_d_tampung_by_id($id_tampung,$nominal);  
      }
    }

    public function hapus_potongan()
    {
      $id = $this->input->post('id_potongan');
      $id_tampung = $this->input->post('id_tampung');
      $this->M_potongan_karyawan->delete($id);

      if($id_tampung != '')
      {
        $this->M_gl_hitung_gaji->delete_d_tampung_by_id($id_tampung);
      } 
    }



    public function setaktif_potongan()
    {
      $id = $this->input->post('id_potongan');
      $is_aktif = $this->input->post('is_aktif');

      $tgl = date('Y-m-d H:i:s');

      $data = array(
        'is_aktif' => $is_aktif,
        'tgl_updt' => $tgl,
      );

      $this->M_potongan_karyawan->update($id, $data); 
    }

    public function proses_gaji()
    {
      $periode = $this->input->post('periode');
      $tgl = date('Y-m-d');

      $this->M_gl_hitung_gaji->reset_d_payment($periode);
      $this->M_gl_hitung_gaji->delete_payment($periode);

      //TARIK DATA TUNJANGAN DARI MASTER
      $this->M_gl_hitung_gaji->insert_proses_tunjangan($periode);

      //TARIK DATA POTONGAN DARI MASTER
      $this->M_gl_hitung_gaji->insert_proses_potongan($periode);   

      $list_karyawan = $this->M_gl_hitung_gaji->list_karyawan('');

      foreach ($list_karyawan as $row) {

        $gaji_pokok = $this->M_gl_gaji_pokok->get_gaji_pokok($row->id_karyawan,$row->kode_kantor);
        $total_tunjangan = $this->M_gl_hitung_gaji->total_tunjangan_karyawan($row->id_karyawan,$row->kode_kantor,$periode);
        $total_potongan = $this->M_gl_hitung_gaji->total_potongan_karyawan($row->id_karyawan,$row->kode_kantor,$periode);
        
        $gaji_kotor = $gaji_pokok+$total_tunjangan-$total_potongan;


        //AMBIL DATA POTONGAN YANG JADI PENAMBAH HITUNGAN PAJAK EX : KOPERASI
        $kembalian_pajak = $this->M_gl_hitung_gaji->get_data_kembalian_pajak($row->id_karyawan,$periode);

        //HITUNG PAJAK
        $pph_murni = 0;
        if($row->is_pajak == '1')
        {
          if($row->nama_jabatan != 'DOKTER')
          {
            $gaji_temp = $gaji_kotor + $kembalian_pajak;

            $gaji_setahun = $gaji_temp * 12;
            $biaya_admin = $gaji_setahun * 0.05;
            $ptkp = $row->ptkp;
            $dpp = $gaji_setahun - $biaya_admin - $ptkp;
            $pph_setahun = $dpp * 0.05;
            $pph_sebulan = $pph_setahun / 12;

            if($biaya_admin >= 6000000)
            {
              $dpp = $gaji_setahun - 6000000 - $ptkp;
              $pph_setahun = 50000000 * 0.05;
              $pph_sebulan = $pph_setahun / 12;
                            
              $pph_setahun_15 = ($dpp - 50000000) * 0.15;
              $pph_sebulan_15 = $pph_setahun_15 / 12;

              $ab = $pph_sebulan + $pph_sebulan_15;
              $beban_pt = $ab * 0.5;
              $pph_murni = $beban_pt;

            } else {
              $ab = $pph_sebulan;
              $beban_pt = $ab * 0.5;
              $pph_murni = $beban_pt;          
            }  
          } else {
            $gaji_temp = $gaji_kotor+$kembalian_pajak;

            $pph_murni = $gaji_temp * 0.5;
            $pph_murni = $pph_murni * 0.05;
          }
        }

        
        $gaji_bersih = $gaji_kotor - $pph_murni;

        $data = array(
            'id_karyawan' => $row->id_karyawan,
            'nama_karyawan' => $row->nama_karyawan,
            'no_karyawan' => $row->no_karyawan,
            'jabatan' => $row->nama_jabatan,
            'tgl_payment' => $tgl,
            'periode' => $periode,
            'gaji_pokok' => $gaji_pokok,
            'total_tunjangan' => $total_tunjangan,
            'total_potongan' => $total_potongan,
            'pph_21' => $pph_murni,
            'gaji_kotor' => $gaji_kotor,
            'gaji_bersih' => $gaji_bersih,
            'is_approve' => '0',
            'user_ins' => $this->session->userdata('ses_id_karyawan'),
            'kode_kantor' => $row->kode_kantor,
          );
        $this->M_gl_hitung_gaji->insert_header_payment($data);
      }

    }

    public function laporan()
    {
      if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
      {
        header('Location: '.base_url().'gl-admin-login');
      }
      else
      {
        $periode = $this->uri->segment(2,0);
        $kode_kantor = $this->uri->segment(3,0); 
        $cari = $this->uri->segment(4,0);

        if($cari == '0') {
          $cari = '';
        }

        if($kode_kantor == '0') {
          $kode_kantor = '';
        }

        if($kode_kantor == 'MANAGEMENT')
        {
          $laporan = $this->M_gl_hitung_gaji->laporan_gaji_management($periode,$cari,$kode_kantor);  
        } else if($kode_kantor == 'DOKTER')
        {
          $laporan = $this->M_gl_hitung_gaji->laporan_gaji_dokter($periode,$cari,$kode_kantor);  
        } else
        {
          $laporan = $this->M_gl_hitung_gaji->laporan_gaji_cabang($periode,$cari,$kode_kantor);  
        }

        
        $list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();


        $data = array(
                      'page_content'=>'c_gl_hitung_gaji/laporan_gaji',
                      'data' => $laporan,
                      'periode' => $periode,
                      'list_kantor' => $list_kantor,
                      'kode_kantor' => $kode_kantor,
                      'cari' => $cari,
                    );
        $this->load->view('pusat/container',$data);
      }
    }

    public function detail_penggajian()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);
      $kode_kantor = $this->uri->segment(5,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = '2021-10-01';//$tgl_conv->format('Y-m-24');
      $tgl_to = '2021-10-20';

      $get_karyawan = $this->M_gl_hitung_gaji->get_karyawan($id_karyawan,$kode_kantor);

      if($get_karyawan->nama_jabatan == 'DOKTER')
      {
        if($get_karyawan->rumus_gaji == 1)
        {
          //$list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_new($id_karyawan,$tgl_from,$tgl_to);    
          //$total_tindakan = $this->M_gl_hitung_gaji->hitung_total_tindakan_dokter($id_karyawan,$tgl_from,$tgl_to);
          //$cek_bft = $this->M_gl_hitung_gaji->cek_tindakan_bft($id_karyawan,$tgl_from,$tgl_to);
          $list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_tahap1_baru($id_karyawan,$tgl_from,$tgl_to);    

          $page_content = 'detail_penggajian_v2';
        } else if($get_karyawan->rumus_gaji == 2)
        {
          //$list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_middle($id_karyawan,$tgl_from,$tgl_to);    
          //$total_tindakan = $this->M_gl_hitung_gaji->hitung_total_tindakan_dokter($id_karyawan,$tgl_from,$tgl_to);
          //$cek_bft = $this->M_gl_hitung_gaji->cek_tindakan_bft($id_karyawan,$tgl_from,$tgl_to);
          $list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_tahap1_lama($id_karyawan,$tgl_from,$tgl_to);    

          $page_content = 'detail_penggajian_v2';
        }  else {
          //$list_data = $this->M_gl_hitung_gaji->detail_penggajian($id_karyawan,$tgl_from,$tgl_to);    
          //$total_tindakan = $this->M_gl_hitung_gaji->hitung_total_tindakan_dokter($id_karyawan,$tgl_from,$tgl_to);
          //$cek_bft = $this->M_gl_hitung_gaji->cek_tindakan_bft($id_karyawan,$tgl_from,$tgl_to);
          $list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_tahap2($id_karyawan,$tgl_from,$tgl_to);    
          
          $page_content = 'detail_penggajian_v2';
        }
      } else {
        $list_data = $this->M_gl_hitung_gaji->detail_ins_terapis($id_karyawan,$tgl_from,$tgl_to);  
        $page_content = 'detail_ins_terapis';
        $total_tindakan = 0;
      }

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/'.$page_content,
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                      'kode_kantor' => $kode_kantor,
                    );
        $this->load->view('pusat/container',$data);
    }

    public function detail_libur()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $list_data = $this->M_gl_hitung_gaji->detail_libur($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_libur',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );
        $this->load->view('pusat/container',$data);
    }

    public function detail_ins_terapis()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $list_data = $this->M_gl_hitung_gaji->detail_ins_terapis($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_ins_terapis',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );
        $this->load->view('pusat/container',$data);
    }

    public function detail_uang_makan()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $list_data = $this->M_gl_hitung_gaji->detail_uang_makan($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_uang_makan',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );
        $this->load->view('pusat/container',$data);
    }

    public function detail_produk_lebih()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02
      $tgl_to = '2021-09-30';//$this->input->post('tgl_to');

      $list_data = $this->M_gl_hitung_gaji->detail_produk_lebih($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_produk_lebih',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );
        $this->load->view('pusat/container',$data);
    }

    public function detail_tindakan_lebih()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $list_data = $this->M_gl_hitung_gaji->detail_tindakan_lebih($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_tindakan_lebih',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );
        $this->load->view('pusat/container',$data);
    }

    public function detail_konsul_baru()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $list_data = $this->M_gl_hitung_gaji->detail_konsul_baru($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_konsul_baru',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );
        $this->load->view('pusat/container',$data);
    }

    public function detail_konsul_lama()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $list_data = $this->M_gl_hitung_gaji->detail_konsul_lama($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_konsul_lama',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );
        $this->load->view('pusat/container',$data);
    }

    public function detail_upselling()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $list_data = $this->M_gl_hitung_gaji->detail_upselling($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_upselling',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );
        $this->load->view('pusat/container',$data);
    }

    public function export_excel()
    {
      $periode = $this->uri->segment(2,0);
      $laporan = $this->M_gl_hitung_gaji->laporan_gaji($periode,'','');

      $data = array(
                    'data' => $laporan,
                    'periode' => $periode,
                  );
      $this->load->view('pusat/page/c_gl_hitung_gaji/excel_laporan_gaji.html',$data);

    }

    public function cetak_excel_detail_tindakan()
    {

      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);
      $kode_kantor = $this->uri->segment(5,0);


      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = '2021-10-01';//$tgl_conv->format('Y-m-24');
      $tgl_to = '2021-10-20';

      $get_karyawan = $this->M_gl_hitung_gaji->get_karyawan($id_karyawan,$kode_kantor);

      if($get_karyawan->nama_jabatan == 'DOKTER')
      {
        if($get_karyawan->rumus_gaji == 1)
        {
          //$list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_new($id_karyawan,$tgl_from,$tgl_to);    
          //$total_tindakan = $this->M_gl_hitung_gaji->hitung_total_tindakan_dokter($id_karyawan,$tgl_from,$tgl_to);
          //$cek_bft = $this->M_gl_hitung_gaji->cek_tindakan_bft($id_karyawan,$tgl_from,$tgl_to);
          $list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_tahap1_baru($id_karyawan,$tgl_from,$tgl_to);    

          $page_content = 'excel_detail_tindakan_v2';
        } else if($get_karyawan->rumus_gaji == 2)
        {
          //$list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_middle($id_karyawan,$tgl_from,$tgl_to);    
          //$total_tindakan = $this->M_gl_hitung_gaji->hitung_total_tindakan_dokter($id_karyawan,$tgl_from,$tgl_to);
          //$cek_bft = $this->M_gl_hitung_gaji->cek_tindakan_bft($id_karyawan,$tgl_from,$tgl_to);
          $list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_tahap1_lama($id_karyawan,$tgl_from,$tgl_to);    

          $page_content = 'excel_detail_tindakan_v2';
        }  else {
          //$list_data = $this->M_gl_hitung_gaji->detail_penggajian($id_karyawan,$tgl_from,$tgl_to);    
          //$total_tindakan = $this->M_gl_hitung_gaji->hitung_total_tindakan_dokter($id_karyawan,$tgl_from,$tgl_to);
          //$cek_bft = $this->M_gl_hitung_gaji->cek_tindakan_bft($id_karyawan,$tgl_from,$tgl_to);
          $list_data = $this->M_gl_hitung_gaji->detail_ins_dokter_tahap2($id_karyawan,$tgl_from,$tgl_to);    
          
          $page_content = 'excel_detail_tindakan_v2';
        }
      } else {
        $list_data = $this->M_gl_hitung_gaji->detail_ins_terapis($id_karyawan,$tgl_from,$tgl_to);  
        $page_content = 'excel_detail_terapis';
      }

      $data = array(
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                      'kode_kantor' => $kode_kantor,
                    );

        $this->load->view('pusat/page/c_gl_hitung_gaji/'.$page_content.'.html',$data);
    }

    public function cetak_excel_detail_tindakan_mentah()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);
      $kode_kantor = $this->uri->segment(5,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = '2021-10-01';//$tgl_conv->format('Y-m-24');
      $tgl_to = '2021-10-20';

      $get_karyawan = $this->M_gl_hitung_gaji->get_karyawan($id_karyawan,$kode_kantor);

      if($get_karyawan->nama_jabatan == 'DOKTER')
      {
        if($get_karyawan->rumus_gaji == 1)
        {
          $list_data = $this->M_gl_hitung_gaji->detail_dokter_mentah_tahap1_baru($id_karyawan,$tgl_from,$tgl_to);    
        } else if($get_karyawan->rumus_gaji == 2)
        {
          $list_data = $this->M_gl_hitung_gaji->detail_dokter_mentah_tahap1_lama($id_karyawan,$tgl_from,$tgl_to);
        }  else {
          $list_data = $this->M_gl_hitung_gaji->detail_dokter_mentah_tahap2($id_karyawan,$tgl_from,$tgl_to);    
        }
      }

      $page_content = 'excel_detail_tindakan_mentah';

      $data = array(
                    'id_karyawan' => $id_karyawan,
                    'periode' => $periode,
                    'tgl_to' => $tgl_to,
                    'data' => $list_data,
                    'kode_kantor' => $kode_kantor,
                  );

        $this->load->view('pusat/page/c_gl_hitung_gaji/'.$page_content.'.html',$data);
    }

    public function excel_konsul_baru()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $list_data = $this->M_gl_hitung_gaji->detail_konsul_baru($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_konsul_baru',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );

      $this->load->view('pusat/page/c_gl_hitung_gaji/excel_konsul_baru.html',$data);
    }

    public function excel_konsul_lama()
    {
      $id_karyawan = $this->uri->segment(2,0);
      $tgl_to = $this->uri->segment(3,0);
      $periode = $this->uri->segment(4,0);

      $tgl_conv = date_create($tgl_to.' first day of last month');
      $tgl_from = $tgl_conv->format('Y-m-24'); //2011-02

      $list_data = $this->M_gl_hitung_gaji->detail_konsul_lama($id_karyawan,$tgl_from,$tgl_to);

      $data = array(
                      'page_content'=>'c_gl_hitung_gaji/detail_konsul_lama',
                      'id_karyawan' => $id_karyawan,
                      'periode' => $periode,
                      'tgl_to' => $tgl_to,
                      'data' => $list_data,
                    );

      $this->load->view('pusat/page/c_gl_hitung_gaji/excel_konsul_lama.html',$data);
    }

    public function cetak_slip_gaji()
    {
      $this->load->library('fpdf');

      $id_payment = $this->uri->segment(2,0);

      $header = $this->M_gl_hitung_gaji->get_header_payment($id_payment);

      $detail = $this->M_gl_hitung_gaji->get_detail_payment($header->id_karyawan,$header->periode);

      $pdf = new FPDF("P","mm","A4");
      $pdf->AddPage();
      $lineHeight=4;
      
      $pdf->SetFont("Arial","B","8");

      //buat border luar
      $pdf->Line(5,12,95,12);   //garis horizontal atas
      $pdf->Line(5,25,95,25);   //garis horizontal atas 2


      $pdf->SetXY(5, 15);
      $pdf->Cell(85,0,'Glafidsya Medika',0,1,'C');
      $pdf->setXY(5,18);
      $pdf->Cell(85,0,'Jl. Perintis Kemerdekaan No. 17 (0263) 281374',0,1,'C');
      $pdf->setXY(5,21);
      $pdf->Cell(85,0,'SLIP GAJI',0,1,'C');

      $pdf->SetFont("Arial","","8");
      $pdf->setXY(5,29);
      $pdf->Cell(100,5,'Tanggal ',0,1,'L');
      $pdf->setXY(40,29);
      $pdf->Cell(100,5,': '.$header->tgl_payment.'',0,1,'L');

      $pdf->setXY(5,33);
      $pdf->Cell(100,5,'Nama ',0,1,'L');
      $pdf->setXY(40,33);
      $pdf->Cell(100,5,': '.$header->nama_karyawan.' ',0,1,'L');

      $pdf->setXY(5,37);
      $pdf->Cell(100,5,'Jabatan ',0,1,'L');
      $pdf->setXY(40,37);
      $pdf->Cell(100,5,': '.$header->jabatan.' ',0,1,'L');

      $pdf->setXY(5,41);
      $pdf->Cell(100,5,'Bulan ',0,1,'L');
      $pdf->setXY(40,41);
      $pdf->Cell(100,5,': '.$header->periode.' ',0,1,'L');

      //LIST PENDAPATAN
      $pdf->setXY(60,45);
      $pdf->SetFont("Arial","B","8");
      $pdf->Cell(60,5,'Pendapatan ',0,1,'L');
      $pdf->Line(60,50,95,50);   //garis vertikal kiri
      
      $pdf->SetFont("Arial","","8");
      $pdf->setXY(5,52);
      $pdf->Cell(50,5,'Gaji Pokok' ,0,1,'L');
      $pdf->setXY(60,52);
      $pdf->Cell(10,5,'Rp',0,1,'L');      
      $pdf->setXY(60,52);
      $pdf->Cell(30,5, number_format($header->gaji_pokok),0,1,'R');        

      $startY = 54;
      $incY = 2;

      foreach ($detail as $d) {
        if($d->jenis_akun == 'DEBIT')
        {

          $pdf->setXY(5,$startY + $incY);
          $pdf->Cell(50,5, $d->nama_akun ,0,1,'L');
          $pdf->setXY(60,$startY + $incY);
          $pdf->Cell(10,5,'Rp',0,1,'L');      
          $pdf->setXY(60,$startY + $incY);

          if($d->nominal == '0')
          {
            $pdf->Cell(30,5, '-',0,1,'R');
          } else {
            $pdf->Cell(30,5, number_format($d->nominal),0,0,'R');  
            $pdf->Cell(7,5, '',0,0,'L');  
            $pdf->Cell(40,5, $d->keterangan,0,1,'L');  
          }
          

          $incY += 4;
        }
      }

      $incY += 3;

      $pdf->Line(60,$startY + $incY,95,$startY + $incY );

      $incY += 2;

      $pdf->SetFont("Arial","B","8");
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(10,5,'Rp',0,1,'L');
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(30,5, number_format($header->total_tunjangan+$header->gaji_pokok),0,1,'R');
      $pdf->SetFont("Arial","","8");

      $incY +=8;

      //LIST POTONGAN
      $pdf->setXY(60,$startY + $incY);
      $pdf->SetFont("Arial","B","8");
      $pdf->Cell(60,5,'Potongan ',0,1,'L');
      $pdf->SetFont("Arial","","8");

      $incY +=5;
      $pdf->Line(60,$startY + $incY,85,$startY + $incY);

      $incY +=4;

      foreach ($detail as $d) {
        if($d->jenis_akun == 'KREDIT')
        {
          $pdf->setXY(5,$startY + $incY);
          $pdf->Cell(50,5, $d->nama_akun ,0,1,'L');
          $pdf->setXY(60,$startY + $incY);
          $pdf->Cell(10,5,'Rp',0,1,'L');      
          $pdf->setXY(60,$startY + $incY);
          
          if($d->nominal == '0')
          {
            $pdf->Cell(30,5, '-',0,1,'R');
          } else {
            $pdf->Cell(30,5, number_format($d->nominal),0,1,'R');  
          }

          $incY += 4;
        }
      }

      $pdf->SetFont("Arial","B","8");
      $pdf->Line(60,$startY + $incY,95,$startY + $incY );


      $incY += 2;
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(10,5,'Rp',0,1,'L');
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(30,5, number_format($header->total_potongan),0,1,'R');


      $incY +=10;

      $pdf->setXY(5,$startY + $incY);
      $pdf->Cell(50,5, 'Total' ,0,1,'L');
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(10,5,'Rp',0,1,'L');
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(30,5, number_format($header->gaji_pokok+$header->total_tunjangan-$header->total_potongan),0,1,'R');

      $incY +=4;

      $pdf->setXY(5,$startY + $incY);
      $pdf->Cell(50,5, 'Pajak PPh 21' ,0,1,'L');
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(10,5,'Rp',0,1,'L');      
      $pdf->setXY(60,$startY + $incY);

      if($header->pph_21 == '0')
      {
        $pdf->Cell(30,5, '-',0,1,'R');
      } else {
        $pdf->Cell(30,5, number_format($header->pph_21),0,1,'R');  
      }

      
      $incY +=8;

      $pdf->setXY(5,$startY + $incY);
      $pdf->Cell(50,5, 'TOTAL GAJI YANG DITERIMA' ,0,1,'L');
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(10,5,'Rp',1,1,'L');      
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(30,5, number_format($header->gaji_bersih),1,1,'R');      

      $pdf->SetFont("Arial","","8");
      
      //APPROVAL
      $incY +=10;

      $pdf->setXY(5,$startY + $incY);
      $pdf->Cell(50,5, 'Menyetujui, ' ,0,1,'L');

      $incY +=5;
      $pdf->setXY(5,$startY + $incY);
      $pdf->Cell(50,5, 'Direktur Utama, ' ,0,1,'L');
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(50,5, 'Accounting, ' ,0,1,'L');



      $incY +=15;
      $pdf->setXY(5,$startY + $incY);
      $pdf->Cell(50,5, 'dr. Reza Gladys ' ,0,1,'L');
      $pdf->setXY(60,$startY + $incY);
      $pdf->Cell(50,5, 'Hetty Sigarlaki' ,0,1,'L');

      $pdf->Line(5,12,5,$startY + $incY+10);   //garis vertikal kiri
      $pdf->Line(95,12,95,$startY + $incY+10);   //garis vertikal kanan
      $pdf->Line(5,$startY + $incY+10,95,$startY + $incY+10);   //garis horizontal bawah

      $pdf->Output($header->nama_karyawan.'.pdf','D');
    }

    public function cetak_slip_tiga()
    {
        $id1 = $this->uri->segment(2,0);
        $id2 = $this->uri->segment(3,0);
        $id3 = $this->uri->segment(4,0);

        $left = 5;

        $arrId = array($id1,$id2,$id3);

        $this->load->library('fpdf');
        $pdf = new FPDF("L","mm","A4");
        $pdf->AddPage();

       for($i=0; $i < count($arrId); $i++) {
          $header = $this->M_gl_hitung_gaji->get_header_payment($arrId[$i]);

          $detail = $this->M_gl_hitung_gaji->get_detail_payment($header->id_karyawan,$header->periode);

          $lineHeight=4;
          
          $pdf->SetFont("Arial","B","8");

          //buat border luar
          $pdf->Line($left,5,$left + 90,5);   //garis horizontal atas
          $pdf->Line($left,15,$left + 90,15);   //garis horizontal atas 2


          $pdf->SetXY($left, 7);
          $pdf->Cell(85,0,'Glafidsya Medika',0,1,'C');
          $pdf->setXY($left,10);
          $pdf->Cell(85,0,'Jl. Perintis Kemerdekaan No. 17 (0263) 281374',0,1,'C');
          $pdf->setXY($left,13);
          $pdf->Cell(85,0,'SLIP GAJI',0,1,'C');

          $pdf->SetFont("Arial","","8");
          $pdf->setXY($left,19);
          $pdf->Cell(100,5,'Tanggal ',0,1,'L');
          $pdf->setXY($left + 35,19);
          $pdf->Cell(100,5,': '.$header->tgl_payment.'',0,1,'L');

          $pdf->setXY($left,23);
          $pdf->Cell(100,5,'Nama ',0,1,'L');
          $pdf->setXY($left + 35,23);
          $pdf->Cell(100,5,': '.$header->nama_karyawan.' ',0,1,'L');

          $pdf->setXY($left,27);
          $pdf->Cell(100,5,'Jabatan ',0,1,'L');
          $pdf->setXY($left + 35,27);
          $pdf->Cell(100,5,': '.$header->jabatan.' ',0,1,'L');

          $pdf->setXY($left,31);
          $pdf->Cell(100,5,'Bulan ',0,1,'L');
          $pdf->setXY($left + 35,31);
          $pdf->Cell(100,5,': '.$header->periode.' ',0,1,'L');

          //LIST PENDAPATAN
          $pdf->setXY($left + 55,35);
          $pdf->SetFont("Arial","B","8");
          $pdf->Cell(60,5,'Pendapatan ',0,1,'L');
          $pdf->Line($left + 55,40,$left + 90,40);   //garis vertikal kiri
          
          $pdf->SetFont("Arial","","8");
          $pdf->setXY($left,42);
          $pdf->Cell(50,5,'Gaji Pokok' ,0,1,'L');
          $pdf->setXY($left + 55,42);
          $pdf->Cell(10,5,'Rp',0,1,'L');      
          $pdf->setXY($left + 55,42);
          $pdf->Cell(30,5, number_format($header->gaji_pokok),0,1,'R');        

          $startY = 44;
          $incY = 1.5;

          foreach ($detail as $d) {
            if($d->jenis_akun == 'DEBIT')
            {

              $pdf->setXY($left,$startY + $incY);
              $pdf->Cell(50,5, $d->nama_akun ,0,1,'L');
              $pdf->setXY($left + 55,$startY + $incY);
              $pdf->Cell(10,5,'Rp',0,1,'L');      
              $pdf->setXY($left + 55,$startY + $incY);

              if($d->nominal == '0')
              {
                $pdf->Cell(30,5, '-',0,1,'R');
              } else {
                $pdf->Cell(30,5, number_format($d->nominal),0,0,'R');  
                $pdf->Cell(7,5, '',0,0,'L');  
                $pdf->Cell(40,5, $d->keterangan,0,1,'L');  
              }
              

              $incY += 3.5;
            }
          }

          $incY += 2;

          $pdf->Line($left + 55,$startY + $incY,$left + 90,$startY + $incY );

          $incY += 1.5;

          $pdf->SetFont("Arial","B","8");
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(10,5,'Rp',0,1,'L');
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(30,5, number_format($header->total_tunjangan+$header->gaji_pokok),0,1,'R');
          $pdf->SetFont("Arial","","8");

          $incY +=4;

          //LIST POTONGAN
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->SetFont("Arial","B","8");
          $pdf->Cell(60,5,'Potongan ',0,1,'L');
          $pdf->SetFont("Arial","","8");

          $incY +=4;
          $pdf->Line($left + 55,$startY + $incY,$left + 90,$startY + $incY);

          $incY +=3;

          foreach ($detail as $d) {
            if($d->jenis_akun == 'KREDIT')
            {
              $pdf->setXY($left,$startY + $incY);
              $pdf->Cell(50,5, $d->nama_akun ,0,1,'L');
              $pdf->setXY($left + 55,$startY + $incY);
              $pdf->Cell(10,5,'Rp',0,1,'L');      
              $pdf->setXY($left + 55,$startY + $incY);
              
              if($d->nominal == '0')
              {
                $pdf->Cell(30,5, '-',0,1,'R');
              } else {
                $pdf->Cell(30,5, number_format($d->nominal),0,1,'R');  
              }

              $incY += 3.5;
            }
          }

          $pdf->SetFont("Arial","B","8");
          $pdf->Line($left + 55,$startY + $incY,$left + 90,$startY + $incY );


          $incY += 2;
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(10,5,'Rp',0,1,'L');
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(30,5, number_format($header->total_potongan),0,1,'R');


          $incY +=6;

          $pdf->setXY($left,$startY + $incY);
          $pdf->Cell(50,5, 'Total' ,0,1,'L');
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(10,5,'Rp',0,1,'L');
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(30,5, number_format($header->gaji_pokok+$header->total_tunjangan-$header->total_potongan),0,1,'R');

          $incY +=3;

          $pdf->setXY($left,$startY + $incY);
          $pdf->Cell(50,5, 'Pajak PPh 21' ,0,1,'L');
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(10,5,'Rp',0,1,'L');      
          $pdf->setXY($left + 55,$startY + $incY);

          if($header->pph_21 == '0')
          {
            $pdf->Cell(30,5, '-',0,1,'R');
          } else {
            $pdf->Cell(30,5, number_format($header->pph_21),0,1,'R');  
          }

          
          $incY +=4;

          $pdf->setXY($left,$startY + $incY);
          $pdf->Cell(50,5, 'TOTAL GAJI YANG DITERIMA' ,0,1,'L');
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(10,5,'Rp',1,1,'L');      
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(30,5, number_format($header->gaji_bersih),1,1,'R');      

          $pdf->SetFont("Arial","","8");
          
          //APPROVAL
          $incY +=4;

          $pdf->setXY($left,$startY + $incY);
          $pdf->Cell(50,5, 'Menyetujui, ' ,0,1,'L');

          $incY +=3;
          $pdf->setXY($left,$startY + $incY);
          $pdf->Cell(50,5, 'Direktur Utama, ' ,0,1,'L');
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(50,5, 'Accounting, ' ,0,1,'L');

          $incY +=14;
          $pdf->setXY($left,$startY + $incY);
          $pdf->Cell(50,5, 'dr. Reza Gladys ' ,0,1,'L');
          $pdf->setXY($left + 55,$startY + $incY);
          $pdf->Cell(50,5, 'Hetty Sigarlaki' ,0,1,'L');

          $pdf->Line($left,5,$left,$startY + $incY+10);   //garis vertikal kiri
          $pdf->Line($left + 90,5,$left + 90,$startY + $incY+10);   //garis vertikal kanan
          $pdf->Line($left,$startY + $incY+10,$left + 90,$startY + $incY+10);   //garis horizontal bawah

          $left = $left + 95;

        }

        
        $pdf->Output('Laporan Gaji.pdf','D');
    }

    public function closing_gaji()
    {
      $periode = $this->input->post('periode');

      $cek = $this->M_gl_hitung_gaji->cek_closing($periode);

      if($cek == '0')
      {
        $data = array(
          'nama_closing' => 'PAYMENT',
          'tgl_closing' => date('Y-m-d'),
          'periode' => $periode,
          'stat_closing' => '1',
          'user_ins' => $this->session->userdata('ses_id_karyawan'),
        );

        $this->M_gl_hitung_gaji->closing_gaji($data);
      }

      echo 'ok';

    }

    public function cek_closing()
    {
      $periode = $this->input->post('periode');

      $cek = $this->M_gl_hitung_gaji->cek_closing($periode);

      echo $cek;      
    }
}

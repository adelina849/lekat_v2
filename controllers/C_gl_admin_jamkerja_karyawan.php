<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class C_gl_admin_jamkerja_karyawan extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('M_gl_jamkerja_karyawan','M_akun'));
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

            $rawtgl = $this->uri->segment(4,0);
            if(empty($rawtgl) || $rawtgl == '')
            {
              $rawtgl = date('Y-m-d');
            }

            $id_karyawan = $this->uri->segment(2,0);
            $kode_kantor = $this->uri->segment(3,0);

            if(empty($kode_kantor) || $kode_kantor == '')
            {
              $kode_kantor = $this->session->userdata('ses_kode_kantor');
            }

            $karyawan = $this->M_gl_jamkerja_karyawan->get_karyawan($id_karyawan,$kode_kantor);

            $tgl1 = date("Y-m-01", strtotime($rawtgl));
            $tgl2 = date("Y-m-t", strtotime($rawtgl));
            $list_jamkerja = $this->M_gl_jamkerja_karyawan->list_jamkerja();

            $raw_data = $this->M_gl_jamkerja_karyawan->get_all($id_karyawan,$tgl1,$tgl2,$kode_kantor);

            if(empty($raw_data))
            {
               $date1=date_create($tgl1);
               $date2=date_create($tgl2);

               $diff= date_diff($date1,$date2);

               $count = $diff->format('%a');

               //echo $count;
               //insert dlu tgl 1 baru sisanya looping d bawah
               $this->M_gl_jamkerja_karyawan->simpan_jam_kerja_monthly($id_karyawan,$kode_kantor,date_format($date1,"Y-m-d"));

               for($i=0;$i<$count;$i++)
               {
                  //echo $i;
                  date_add($date1,date_interval_create_from_date_string("1 days"));

                  $this->M_gl_jamkerja_karyawan->simpan_jam_kerja_monthly($id_karyawan,$kode_kantor,date_format($date1,"Y-m-d"));
               }
               //echo $diff->format('%a')+1;

               $raw_data = $this->M_gl_jamkerja_karyawan->get_all($id_karyawan,$tgl1,$tgl2,$kode_kantor);
            }

            $jam_kerja = json_encode($raw_data);
            $list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();

            $data = array(
                      'page_content'=>'c_gl_jamkerja_karyawan/gl_admin_jamkerja_karyawan',
                      'tgl' => $rawtgl,
                      'data' => $jam_kerja,
                      'karyawan' => $karyawan,
                      'list_jamkerja' => $list_jamkerja,
                      'kode_kantor' => $kode_kantor,
                      'list_kantor' => $list_kantor,
                    );
            if( ($this->session->userdata('ses_akses_lvl2_54') > 0))
            {
              $this->load->view('admin/container',$data);
            } else {
              $this->load->view('pusat/container',$data);  
            }
            
          }
          else
          {
            header('Location: '.base_url().'gl-admin-login');
          }
        }
    }

    public function list_jam_karyawan()
    {
      $tgl = $this->input->post('tgl');
      $cari = $this->input->post('cari');
      $id_karyawan = $this->input->post('id_karyawan');
      $kode_kantor = $this->input->post('kode_kantor');
      
      $list_jamkerja = $this->M_gl_jamkerja_karyawan->list_jamkerja_karyawan($tgl,$id_karyawan,$kode_kantor,$cari);

      $noxx = 1;
      foreach($list_jamkerja as $j)
      {
        echo '<tr>';
        echo '    <td>'.$noxx.'</td>';
        echo '    <td><input type="hidden" id="nama_jam_'.$noxx.'" value="'.$j->nama_jam.'">'.$j->nama_jam.'</td>';
        echo '    <td><input type="hidden" id="jam_masuk_'.$noxx.'" value="'.$j->jam_masuk.'">'.$j->jam_masuk.'</td>';
        echo '    <td><input type="hidden" id="jam_keluar_'.$noxx.'" value="'.$j->jam_keluar.'">'.$j->jam_keluar.'</td>';
        echo '    <td><input type="text" class="form-control" id="lembur_'.$noxx.'" value="'.$j->jam_lembur.'"></td>';
        echo '    <td><button class="btn btn-success" onclick="updateJamKerja('.$noxx.')" >Pilih</button></td>';
        echo '</tr>';
        
        $noxx++;
      }

    }

    public function list_karyawan()
    {
      $cari = $this->input->post('cari');
      $kode_kantor = $this->input->post('kode_kantor');

      $data = $this->M_gl_jamkerja_karyawan->list_karyawan($cari,$kode_kantor);

      $no=1;
      foreach ($data as $row) {
        echo '<tr>';
        echo '<input type="hidden" id="kode_kantor_'.$no.'" value="'.$row->kode_kantor.'" />';
        echo '<td><input type="hidden" id="id_karyawan_'.$no.'" value="'.$row->id_karyawan.'" />'.$no.'</td>';
        echo '<td><input type="hidden" id="no_karyawan_'.$no.'" value="'.$row->no_karyawan.'" />'.$row->no_karyawan.'</td>';
        echo '<td><input type="hidden" id="nama_karyawan_'.$no.'" value="'.$row->nama_karyawan.'" />'.$row->nama_karyawan.'</td>';
        echo '<td><input type="hidden" id="nama_kantor_'.$no.'" value="'.$row->kode_kantor.'" />'.$row->kode_kantor.'</td>';
        echo '<td><button type="button" class="btn btn-info" data-dismiss="modal" onclick="pilihKaryawan('.$no.')">Pilih</button></td>';
        echo '</tr>';
        $no++;
      }
    }

    public function list_shift_karyawan()
    {
      $cari = $this->input->post('cari');
      $kode_kantor = $this->input->post('kode_kantor');

      $data = $this->M_gl_jamkerja_karyawan->list_shift_karyawan($kode_kantor,$cari);

      $list_jamkerja = $this->M_gl_jamkerja_karyawan->list_jamkerja();

      $no=1;
      foreach ($data as $row) {
        echo '<tr>';
        echo '<td><input type="hidden" id="id_shift_karyawan_'.$no.'" value="'.$row->id_shift_karyawan.'">'.$no.'</td>';
        echo '<input type="hidden" id="kode_kantor_'.$no.'" value="'.$row->kode_kantor.'">';
        echo '<td><input type="hidden" id="id_karyawan_'.$no.'" value="'.$row->id_karyawan.'"> '.$row->no_karyawan.'</td>';
        echo '<td>'.$row->nama_karyawan.'</td>';
        
        echo '<td><select class="form-control" id="id_jam_shift_'.$no.'" onchange="updateShiftKaryawan('.$no.')">';
        echo '<option value="">  </option> ';

        foreach ($list_jamkerja as $row2) 
        {
           echo '<option ';
           if($row2->id_jam == $row->id_jam) echo 'selected';
           echo ' value="'.$row2->id_jam.'">'.$row2->nama_jam.' '.$row2->jam_masuk.'-'.$row2->jam_keluar.'</option>';
        }
        echo '</select></td>';

        echo '</tr>';

        $no++;
      }
    }

    public function update_shift_karyawan()
    {
        $id_shift_karyawan = $this->input->post('id_shift_karyawan');
        $id_karyawan = $this->input->post('id_karyawan');
        $kode_kantor = $this->input->post('kode_kantor');
        $id_jam_shift = $this->input->post('id_jam_shift');

        $tgl = date('Y-m-d H:i:s');

        //if($id_shift_karyawan != '')
        //{
          $post = array(
                      'id_karyawan' => $id_karyawan,
                      'kode_kantor' => $kode_kantor,
                       );

          $cek_shift_karyawan = $this->M_gl_jamkerja_karyawan->cek_shift_karyawan($post);

          if(empty($cek_shift_karyawan))
          {
            $data = array(
                'id_karyawan' => $id_karyawan,
                'id_jam' => $id_jam_shift,
                'user_ins' => $this->session->userdata('ses_id_karyawan'),
                'kode_kantor' => $kode_kantor,
              );

            $this->M_gl_jamkerja_karyawan->insert_tb_shift_karyawan($data);
          } else {

            $data = array(
                'id_karyawan' => $id_karyawan,
                'id_jam' => $id_jam_shift,
                'tgl_ins' => $tgl,
                'user_ins' => $this->session->userdata('ses_id_karyawan'),
              );          

            $this->M_gl_jamkerja_karyawan->update_tb_shift_karyawan($data,$id_shift_karyawan);
          }
        //}
    }

    public function update_jam_kerja()
    {
      $id_karyawan = $this->input->post('id_karyawan');
      $kode_kantor = $this->input->post('kode_kantor');
      $tanggal = $this->input->post('tanggal');
      $nama_jam = $this->input->post('nama_jam');
      $jam_masuk = $this->input->post('jam_masuk');
      $jam_keluar = $this->input->post('jam_keluar');
      $jam_lembur = $this->input->post('jam_lembur');

      $cek = $this->M_gl_jamkerja_karyawan->cek_jamkerja($id_karyawan,$kode_kantor,$tanggal);

      if(empty($cek))
      {
        $post = array(
              'id_karyawan' => $id_karyawan,
              'tanggal' => $tanggal,
              'nama_jam' => $nama_jam,
              'jam_masuk' => $jam_masuk,
              'jam_keluar' => $jam_keluar,
              'jam_lembur' => $jam_lembur,
              'user_ins' => $this->session->userdata('ses_id_karyawan'),
              'kode_kantor' => $kode_kantor,
            );
        $this->M_gl_jamkerja_karyawan->insert_jamkerja($post);
      } else {
          $tgl = date('Y-m-d H:i:s');
          $data = array(
                'tanggal' => $tanggal,
                'nama_jam' => $nama_jam,
                'jam_masuk' => $jam_masuk,
                'jam_keluar' => $jam_keluar,
                'jam_lembur' => $jam_lembur,
                'tgl_updt' => $tgl,
                'user_updt' => $this->session->userdata('ses_id_karyawan'),
                );
          $this->M_gl_jamkerja_karyawan->update_jamkerja($data,$id_karyawan,$kode_kantor,$tanggal);
      }
    }

    public function hapus_jam_kerja()
    {
      $id_karyawan = $this->input->post('id_karyawan');
      $kode_kantor = $this->input->post('kode_kantor');
      $tanggal = $this->input->post('tanggal');

      $post = array(
            'id_karyawan' => $id_karyawan, 
            'kode_kantor' => $kode_kantor, 
            'tanggal' => $tanggal, 
          );

      $this->M_gl_jamkerja_karyawan->delete_jamkerja($post);
    }

    public function hapus_jam_kerja_bulanan()
    {
      $id_karyawan = $this->input->post('id_karyawan');
      $kode_kantor = $this->input->post('kode_kantor');
      $periode = $this->input->post('periode');

      $this->M_gl_jamkerja_karyawan->delete_jamkerja_bulanan($id_karyawan,$periode,$kode_kantor);
    }


}

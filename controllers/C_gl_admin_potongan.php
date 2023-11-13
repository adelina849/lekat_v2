<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class C_gl_admin_potongan extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(array('M_gl_potongan','M_akun'));
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

            $data = array('page_content'=>'c_gl_admin_potongan/gl_admin_potongan');
            $this->load->view('pusat/container',$data);
          }
          else
          {
            header('Location: '.base_url().'gl-admin-login');
          }
        }
    }

    public function list_potongan()
    {
      $cari = $this->input->post('cari');
      $data = $this->M_gl_potongan->get_like($cari);

      $no=1;
      foreach ($data as $row) {
        echo '<tr>';
        echo '<td><input type="hidden" id="id_potongan_'.$no.'" value="'.$row->id_potongan.'" />'.$no.'</td>';
        echo '<td><input type="hidden" id="kode_potongan_'.$no.'" value="'.$row->kode_potongan.'" />'.$row->kode_potongan.'</td>';
        echo '<td><input type="hidden" id="nama_potongan_'.$no.'" value="'.$row->nama_potongan.'" />'.$row->nama_potongan.'</td>';
        echo '<td><button type="button" class="btn btn-info" data-dismiss="modal" onclick="pilihPotongan('.$no.')">Pilih</button></td>';
        echo '</tr>';
        $no++;
      }

    }

    public function json() {
        header('Content-Type: application/json');
        echo $this->M_gl_potongan->json();
    }

    public function create()
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
          $data = array(
              'button' => 'Create',
              'action' => site_url('c_gl_admin_potongan/create_action'),
        	    'id_potongan' => set_value('id_potongan'),
              'kode_potongan' => set_value('kode_potongan'),
        	    'nama_potongan' => set_value('nama_potongan'),
        	    'nominal' => set_value('nominal'),
        	    'ket_potongan' => set_value('ket_potongan'),
              'page_content'=>'c_gl_admin_potongan/gl_admin_input_potongan',
              'title' => 'Input Master Potongan',
        	);
          $this->load->view('pusat/container',$data);
        }
        else
        {
          header('Location: '.base_url().'gl-admin-login');
        }
      }
    }

    public function create_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->create();
        } else {
            $data = array(
              'kode_potongan' => $this->input->post('kode_potongan',TRUE),
          		'nama_potongan' => $this->input->post('nama_potongan',TRUE),
          		'nominal' => $this->input->post('nominal',TRUE),
          		'ket_potongan' => $this->input->post('ket_potongan',TRUE),
              'user_ins' => $this->session->userdata('ses_id_karyawan'),
              'kode_kantor' => $this->session->userdata('ses_kode_kantor'),
	           );

            $this->M_gl_potongan->insert($data);
            $this->session->set_flashdata('message', 'Create Record Success');
            redirect(site_url('gl-admin-potongan'));
        }
    }

    public function update()
    {
        $id = $this->uri->segment(2,0);

        $row = $this->M_gl_potongan->get_by_id($id);

        if ($row) {
            $data = array(
                'button' => 'Update',
                'action' => site_url('c_gl_admin_potongan/update_action'),
            		'id_potongan' => set_value('id_potongan', $row->id_potongan),
                'kode_potongan' => set_value('kode_potongan', $row->kode_potongan),
            		'nama_potongan' => set_value('nama_potongan', $row->nama_potongan),
            		'nominal' => set_value('nominal', $row->nominal),
            		'ket_potongan' => set_value('ket_potongan', $row->ket_potongan),
                'page_content'=>'c_gl_admin_potongan/gl_admin_input_potongan',
                'title' => 'Update Master Potongan',
	          );
            $this->load->view('pusat/container',$data);
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-potongan'));
        }
    }

    public function update_action()
    {
        $this->_rules();

        if ($this->form_validation->run() == FALSE) {
            $this->update($this->input->post('id_potongan', TRUE));
        } else {
            $data = array(
              'kode_potongan' => $this->input->post('kode_potongan',TRUE),
          		'nama_potongan' => $this->input->post('nama_potongan',TRUE),
          		'nominal' => $this->input->post('nominal',TRUE),
          		'ket_potongan' => $this->input->post('ket_potongan',TRUE),
          		'tgl_ins' => $this->input->post('tgl_ins',TRUE),
          		'tgl_updt' => $this->input->post('tgl_updt',TRUE),
          		'user_ins' => $this->input->post('user_ins',TRUE),
          		'user_updt' => $this->input->post('user_updt',TRUE),
	          );

            $this->M_gl_potongan->update($this->input->post('id_potongan', TRUE), $data);
            $this->session->set_flashdata('message', 'Update Record Success');
            redirect(site_url('gl-admin-potongan'));
        }
    }

    public function delete($id)
    {
        $row = $this->M_gl_potongan->get_by_id($id);

        if ($row) {
            $this->M_gl_potongan->delete($id);
            $this->session->set_flashdata('message', 'Delete Record Success');
            redirect(site_url('gl-admin-potongan'));
        } else {
            $this->session->set_flashdata('message', 'Record Not Found');
            redirect(site_url('gl-admin-potongan'));
        }
    }

    public function _rules()
    {
        $this->form_validation->set_rules('kode_potongan', 'Kode potongan', 'trim|required');
      	$this->form_validation->set_rules('nama_potongan', 'Nama potongan', 'trim|required');
      	$this->form_validation->set_rules('nominal', 'nominal', 'trim|required');
      	$this->form_validation->set_rules('ket_potongan', 'ket potongan', 'trim|required');

      	$this->form_validation->set_rules('id_potongan', 'id_potongan', 'trim');
      	$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');
    }

}

/* End of file C_gl_admin_potongan.php */
/* Location: ./application/controllers/C_gl_admin_potongan.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2020-10-08 06:44:55 */
/* http://harviacode.com */

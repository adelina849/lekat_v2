<?php
	class M_images extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_images_limit($id,$group,$cari,$limit,$offset)
		{
			$query = $this->db->query("
										SELECT * FROM tb_images WHERE id = '". $id ."' AND group_by = '". $group ."'
										".$cari." ORDER BY tgl_ins ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_images_limit($id,$group,$cari)
		{
			$query = $this->db->query("SELECT COUNT(id) AS JUMLAH FROM tb_images WHERE id = '". $id ."' AND group_by = '". $group ."'
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
			$id
			,$group_by
			,$nama
			,$foto
			,$foto_url
			,$ket
			,$kode_kantor
			,$user_updt
		)
		{
			$date = date('Y-m-d'); 
			$jam = date('Y-m-d H:i:s'); 
			$data = array
			(
			   'id' => $id,
			   'group_by' => $group_by,
			   'img_nama' => $nama,
			   'img_file' => $foto,
			   'img_url' => $foto_url,
			   'ket_img' => $ket,
			   'tgl_ins' => $jam,
			   'tgl_updt' => $jam,
			   'user_updt' => $user_updt,
			   'kode_kantor' => $kode_kantor
			);

			$this->db->insert('tb_images', $data); 
		}
		
		function edit_with_image
		(
			$id_images
			,$id
			,$group_by
			,$nama
			,$foto
			,$foto_url
			,$ket
			,$user_updt
		)
		{
			$date = date('Y-m-d'); 
			$jam = date('Y-m-d H:i:s'); 
			$data = array
			(
			   'id' => $id,
			   'group_by' => $group_by,
			   'img_nama' => $nama,
			   'img_file' => $foto,
			   'img_url' => $foto_url,
			   'ket_img' => $ket,
			   'tgl_updt' => $jam,
			   'user_updt' => $user_updt
			);
			
			$this->db->where('id_images', $id_images);
			$this->db->update('tb_images', $data);
		}
		
		function edit_no_image
		(
			$id_images
			,$id
			,$group_by
			,$nama
			,$ket
			,$user_updt
		)
		{
			$date = date('Y-m-d'); 
			$jam = date('Y-m-d H:i:s'); 
			$data = array
			(
			   'id' => $id,
			   'group_by' => $group_by,
			   'img_nama' => $nama,
			   'ket_img' => $ket,
			   'tgl_updt' => $jam,
			   'user_updt' => $user_updt,
			);
			
			$this->db->where('id_images', $id_images);
			$this->db->update('tb_images', $data);
		}
		
		function hapus($id)
		{
			$this->db->query('DELETE FROM tb_images WHERE id_images = '.$id.'');
		}
		
		function get_images($id,$group_by,$berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_images', array('id'=>$id,'group_by'=>$group_by,$berdasarkan => $cari));
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
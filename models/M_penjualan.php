<?php
	class M_penjualan extends CI_Model
	{

		public $table = 'tb_h_penjualan';
	    public $id = 'id_h_penjualan';
	    public $order = 'DESC';

		function __construct()
		{
			parent::__construct();
		}

		function get_id_penjualan($id_penjualan)
		{
			$query = $this->db->query("
				SELECT *
				FROM tb_h_penjualan
				WHERE id_h_penjualan = '".$id_penjualan."'
				");

			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}

		// get data by id
	    function get_by_id($id)
	    {
	        $this->db->where($this->id, $id);
	        return $this->db->get($this->table)->row();
	    }

	    function get_where($data)
	    {
	        $this->db->where($data);
	        return $this->db->get($this->table)->row();
	    }

	    function get_all_where($id_h_penjualan)
	    {
	        $data = $this->db->query("SELECT A.id_h_penjualan, A.id_costumer, A.no_faktur, tgl_h_penjualan, 
	        						  A.bayar + A.kode_unik AS bayar,'' ket_h_pembelian, B.nama_lengkap,C.telepon as hp,
	        						  CONCAT(C.detail_alamat,' ',C.kecamatan,' ',C.kabkota,' ',C.provinsi) as alamat,
	        						  ket_penjualan,tgl_pengiriman,ongkir,nama_kat_member
									  FROM tb_h_penjualan A
									  LEFT JOIN tb_member B ON A.id_costumer = B.id_member
									  LEFT JOIN tb_alamat C ON A.id_alamat = C.id_alamat
									  LEFT JOIN tb_kat_member D ON B.id_kat_member = D.id_kat_member
									  WHERE id_h_penjualan = '".$id_h_penjualan."'
			")->row();
	        return $data;
	    }

	    function get_all_where_langsung($no_faktur)
	    {
	        $data = $this->db->query("
	            SELECT A.id_h_penjualan,
	                   A.id_costumer,
	                   nama_lengkap,
	                   nama_kat_member,
	                   no_faktur,
	                   ongkir,
	                   tgl_h_penjualan,
	                   A.id_alamat,
	                   ket_penjualan,
	                   bayar,
	                   kode_kurir,
	                   no_resi,
	                   kurir,
	                   paket,
	                   alamat,
	                   kecamatan,
	                   kabkota,
	                   provinsi,
	                   isGratis,
	                   SUM(jumlah * harga) as subtotal
	            FROM tb_h_penjualan A
	            LEFT JOIN tb_d_penjualan B
	              ON A.id_h_penjualan = B.id_h_penjualan
	            LEFT JOIN tb_member D
	              ON A.id_costumer = D.id_member
	            LEFT JOIN tb_kat_member E 
	              ON D.id_kat_member = E.id_kat_member
	            WHERE A.no_faktur = '".$no_faktur."'
	            GROUP BY A.id_h_penjualan,
	                   A.id_costumer,
	                   nama_lengkap,
	                   no_faktur,
	                   ongkir,
	                   tgl_h_penjualan,
	                   A.id_alamat,
	                   ket_penjualan,
	                   bayar,
	                   kode_kurir,
	                   no_resi,
	                   kurir,
	                   paket,
	                   alamat,
	                   kecamatan,
	                   kabkota,
	                   provinsi,
	                   isGratis
	        ")->row();

	        return $data;
	    }



	    function list_d_penjualan_langsung($id_h_penjualan)
	    {
	        $data = $this->db->query("
	            SELECT A.id_d_penjualan,
	            	   A.id_kat_produk,
	            	   nama_produk,
	            	   jumlah,
	            	   harga,
	            	   jumlah * harga as subtotal
	            FROM tb_d_penjualan A
	            LEFT JOIN tb_h_penjualan B
	              ON A.id_h_penjualan = B.id_h_penjualan
	            LEFT JOIN tb_produk_toko C
	              ON A.id_produk = C.id_produk_toko
	            WHERE A.id_h_penjualan = '".$id_h_penjualan."'
	        ")->result();

	        return $data;
	    }

	    function get_no_faktur()
		{
			$query = $this->db->query(
			"
	        SELECT CONCAT('POSKN',FRMTGL,ORD) AS no_faktur
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
	            CAST(MID(NOW(),3,2) AS CHAR) AS Y,
	            CAST(MID(NOW(),6,2) AS CHAR) AS M,
	            MID(NOW(),9,2) AS D,
	            COALESCE(MAX(CAST(RIGHT(no_faktur,5) AS UNSIGNED)) + 1,1) AS ORD
	            From tb_h_penjualan
	            WHERE DATE(tgl_ins) = DATE(NOW())
	          ) AS A
	        ) AS AA
			");

			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}

		function get_id_h_penjualan()
		{
			$query = $this->db->query(
			"
	        SELECT CONCAT('SKN',FRMTGL,ORD) AS id_h_penjualan
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
	            COALESCE(MAX(CAST(RIGHT(id_h_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
	            From tb_h_penjualan
	            WHERE DATE(tgl_ins) = DATE(NOW())
	          ) AS A
	        ) AS AA
			")->row();

			return $query;
		}

		function get_id_d_penjualan()
		{
			$query = $this->db->query(
			"
	        SELECT CONCAT('SKND',FRMTGL,ORD) AS id_d_penjualan
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
	          ) AS A
	        ) AS AA
			");

			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}


		function list_order_limit($tgl_from,$tgl_to,$cari,$limit,$offset)
		{
			$query = $this->db->query("
					SELECT A.id_h_penjualan,id_costumer,no_member,nama_lengkap,no_faktur,tgl_h_penjualan,tgl_pengiriman,tgl_sampai,status_penjualan,
					       SUM(jumlah * harga) + ongkir AS bayar
					FROM
					(
						SELECT id_h_penjualan,id_costumer,no_member,B.nama_lengkap,no_faktur,tgl_h_penjualan,tgl_pengiriman,tgl_sampai,
						CASE WHEN sts_penjualan = 'PESAN' AND ongkir = '0' AND isGratis = '0' AND isNanti = '0' THEN 'Menunggu Verifikasi Ongkos Kirim'
							WHEN sts_penjualan = 'PESAN' AND (ongkir <> '0' OR isGratis = '1' OR isNanti = '1') AND bukti_transfer = '' THEN 'Menunggu Pembayaran'
							WHEN sts_penjualan = 'PESAN' AND bukti_transfer <> '' THEN 'Menunggu Verifikasi Pembayaran'
							WHEN sts_penjualan = 'PEMBAYARAN' THEN 'Sedang Diproses'
							WHEN sts_penjualan = 'SELESAI' AND isKirim = '0' THEN 'Sedang Dikirim'
							WHEN sts_penjualan = 'SELESAI' AND isKirim = '1' THEN 'Selesai'
							WHEN sts_penjualan = 'CANCEL' THEN 'Dibatalkan'
							WHEN sts_penjualan = 'SELESAI' AND isKirim = '2' THEN 'Dikembalikan' END AS status_penjualan,ongkir
						FROM tb_h_penjualan A
						LEFT JOIN tb_member B
						   ON A.id_costumer = B.id_member
						LEFT JOIN tb_kat_member C
							ON B.id_kat_member = C.id_kat_member
						".$cari." ORDER BY tgl_h_penjualan ASC LIMIT ".$offset.",".$limit."
					) A LEFT JOIN tb_d_penjualan B
					   ON A.id_h_penjualan = B.id_h_penjualan
					GROUP BY A.id_h_penjualan,id_costumer,no_member,nama_lengkap,no_faktur,tgl_h_penjualan,tgl_pengiriman,tgl_sampai,status_penjualan,ongkir
				"
			);

			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}

		function list_order_member($id_member)
		{
			$query = $this->db->query("
				SELECT id_h_penjualan,no_faktur,nama_lengkap,tgl_h_penjualan,tgl_sampai,status_penjualan,sum(jumlah) as jumlah,
                       sum(jumlah * harga) +ongkir as bayar
                from (
                    SELECT distinct A.id_h_penjualan,no_faktur,nama_lengkap,tgl_h_penjualan,tgl_sampai,status_penjualan,jumlah,
                           sum(harga) as harga,ongkir
                    FROM
                    (
						SELECT id_h_penjualan,id_costumer,no_member,B.nama_lengkap,no_faktur,tgl_h_penjualan,tgl_pengiriman,tgl_sampai,
						CASE WHEN sts_penjualan = 'PESAN' AND ongkir = '0' AND isGratis = '0' AND isNanti = '0' THEN 'Menunggu Verifikasi Ongkos Kirim'
							WHEN sts_penjualan = 'PESAN' AND (ongkir <> '0' OR isGratis = '1' OR isNanti = '1') AND bukti_transfer = '' THEN 'Menunggu Pembayaran'
							WHEN sts_penjualan = 'PESAN' AND bukti_transfer <> '' THEN 'Menunggu Verifikasi Pembayaran'
							WHEN sts_penjualan = 'PEMBAYARAN' THEN 'Sedang Diproses'
							WHEN sts_penjualan = 'SELESAI' AND isKirim = '0' THEN 'Sedang Dikirim'
							WHEN sts_penjualan = 'SELESAI' AND isKirim = '1' THEN 'Selesai'
							WHEN sts_penjualan = 'CANCEL' THEN 'Dibatalkan'
							WHEN sts_penjualan = 'SELESAI' AND isKirim = '2' THEN 'Dikembalikan' END AS status_penjualan,ongkir
						FROM tb_h_penjualan A
						LEFT JOIN tb_member B
						   ON A.id_costumer = B.id_member
						LEFT JOIN tb_kat_member C
							ON B.id_kat_member = C.id_kat_member
						WHERE A.id_costumer = '".$id_member."'
					) A LEFT JOIN tb_d_penjualan B
					   ON A.id_h_penjualan = B.id_h_penjualan
					GROUP BY A.id_h_penjualan,id_costumer,no_member,nama_lengkap,no_faktur,tgl_h_penjualan,tgl_pengiriman,tgl_sampai,status_penjualan,ongkir
				) A group by id_h_penjualan,no_faktur,nama_lengkap,tgl_h_penjualan,tgl_sampai,status_penjualan,ongkir
				"
			)->result();

			return $query;
		}


		function count_order_limit($tgl_from,$tgl_to,$cari)
		{
			$query = $this->db->query("
				SELECT COUNT(id_h_penjualan) AS JUMLAH
				FROM tb_h_penjualan A
				LEFT JOIN tb_member B
					ON A.id_costumer = B.id_member
				LEFT JOIN tb_kat_member C
					ON B.id_kat_member = C.id_kat_member
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

		function list_barang_order($id_penjualan)
		{
			$query = $this->db->query("
					SELECT id_h_penjualan,id_costumer,kode_produk,nama_produk,jumlah,satuan_jual,harga,sts_penjualan,bukti_transfer,no_faktur,
					       biaya_ongkir,bayar+kode_unik as bayar ,kode_kurir,kurir,paket,detail_alamat,kecamatan,kabkota,provinsi,kodepos,isNanti,
					       status_penjualan,kode_stat,no_resi,ket_penjualan,isdrop,kode_unik,isGratis,penerima_alamat,
					       CONCAT('@',FORMAT(harga,'#,###'),' x ',jumlah) ket,(harga*jumlah) AS total,
					       harga*jumlah as total_pure,jumlah
					FROM
					(
						SELECT A.id_h_penjualan,A.id_costumer,'' kode_produk,C.nama_kat AS nama_produk,jumlah,satuan_jual,SUM(harga) AS harga,isNanti,
							   ongkir AS biaya_ongkir,bayar,kode_kurir,kurir,paket,penerima_alamat,E.alamat as detail_alamat,D.kecamatan,E.kabkota,E.provinsi,'' kodepos,
							   CASE WHEN sts_penjualan = 'PESAN' AND ongkir = '0' AND isGratis = '0' AND isNanti = '0' THEN 'Menunggu Verifikasi Ongkos Kirim'
									WHEN sts_penjualan = 'PESAN' AND (ongkir <> '0' OR isGratis = '1' OR isNanti = '1') AND bukti_transfer = '' THEN 'Menunggu Pembayaran'
									WHEN sts_penjualan = 'PESAN' AND bukti_transfer <> '' THEN 'Menunggu Verifikasi Pembayaran'
									WHEN sts_penjualan = 'PEMBAYARAN' THEN 'Sedang Diproses'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '0' THEN 'Sedang Dikirim'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '1' THEN 'Selesai'
									WHEN sts_penjualan = 'CANCEL' THEN 'Dibatalkan'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '2' THEN 'Dikembalikan' END AS status_penjualan,
								CASE WHEN sts_penjualan = 'PESAN' AND ongkir = '0' THEN '0'
									WHEN sts_penjualan = 'PESAN' AND ongkir <> '0' AND bukti_transfer = '' THEN '1'
									WHEN sts_penjualan = 'PESAN' AND ongkir <> '0' AND bukti_transfer <> '' THEN '2'
									WHEN sts_penjualan = 'PEMBAYARAN' THEN '3'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '0' THEN '4'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '1' THEN '5'
									WHEN sts_penjualan = 'CANCEL' THEN '6'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '2' THEN '7' END AS kode_stat,isGratis,
									no_resi,ket_penjualan,isdrop,kode_unik,sts_penjualan,bukti_transfer,no_faktur
						FROM tb_h_penjualan A
						LEFT JOIN tb_d_penjualan B
						  ON A.id_h_penjualan = B.id_h_penjualan
						LEFT JOIN tb_kat_produk_toko C
						  ON B.id_kat_produk = C.id_kat_produk_toko
						LEFT JOIN tb_alamat D
						  ON A.id_alamat = D.id_alamat
						LEFT JOIN tb_member E
						  ON A.id_costumer = E.id_member
						WHERE A.id_h_penjualan = '".$id_penjualan."'
						GROUP BY A.id_h_penjualan,C.nama_kat,jumlah,satuan_jual,isNanti,
							 ongkir,bayar,kode_kurir,kurir,paket,D.detail_alamat,D.kecamatan,kabkota,provinsi,kodepos,sts_penjualan,
							 no_resi,ket_penjualan,isdrop,kode_unik,isGratis,sts_penjualan,bukti_transfer,no_faktur
					) A
			");

			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}

		function list_barang_eceran($id_penjualan)
		{
			$query = $this->db->query("
					SELECT id_h_penjualan,id_costumer,kode_produk,nama_produk,jumlah,satuan_jual,harga,sts_penjualan,bukti_transfer,no_faktur,biaya_ongkir,bayar+kode_unik AS bayar ,kode_kurir,kurir,paket,detail_alamat,kecamatan,kabkota,provinsi,kodepos,
					       status_penjualan,kode_stat,no_resi,ket_penjualan,isdrop,kode_unik,isGratis,penerima_alamat,
					       CONCAT('@',FORMAT(harga,'#,###'),' x ',jumlah) ket,FORMAT((harga*jumlah),'#,###') AS total,
					       harga*jumlah AS total_pure,jumlah
					FROM
					(
						SELECT A.id_h_penjualan,A.id_costumer,kode_produk,nama_produk,jumlah,satuan_jual,SUM(harga) AS harga,
							   ongkir AS biaya_ongkir,bayar,kode_kurir,kurir,paket,penerima_alamat,E.alamat AS detail_alamat,D.kecamatan,E.kabkota,E.provinsi,'' kodepos,
							   CASE WHEN sts_penjualan = 'PESAN' AND ongkir = '0' AND isGratis = '0' AND isNanti = '0' THEN 'Menunggu Verifikasi Ongkos Kirim'
									WHEN sts_penjualan = 'PESAN' AND (ongkir <> '0' OR isGratis = '1' OR isNanti = '1') AND bukti_transfer = '' THEN 'Menunggu Pembayaran'
									WHEN sts_penjualan = 'PESAN' AND bukti_transfer <> '' THEN 'Menunggu Verifikasi Pembayaran'
									WHEN sts_penjualan = 'PEMBAYARAN' THEN 'Sedang Diproses'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '0' THEN 'Sedang Dikirim'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '1' THEN 'Selesai'
									WHEN sts_penjualan = 'CANCEL' THEN 'Dibatalkan'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '2' THEN 'Dikembalikan' END AS status_penjualan,
								CASE WHEN sts_penjualan = 'PESAN' AND ongkir = '0' THEN '0'
									WHEN sts_penjualan = 'PESAN' AND ongkir <> '0' AND bukti_transfer = '' THEN '1'
									WHEN sts_penjualan = 'PESAN' AND ongkir <> '0' AND bukti_transfer <> '' THEN '2'
									WHEN sts_penjualan = 'PEMBAYARAN' THEN '3'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '0' THEN '4'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '1' THEN '5'
									WHEN sts_penjualan = 'CANCEL' THEN '6'
									WHEN sts_penjualan = 'SELESAI' AND isKirim = '2' THEN '7' END AS kode_stat,isGratis,
									no_resi,ket_penjualan,isdrop,kode_unik,sts_penjualan,bukti_transfer,no_faktur
						FROM tb_h_penjualan A
						LEFT JOIN tb_d_penjualan B
						  ON A.id_h_penjualan = B.id_h_penjualan
						LEFT JOIN tb_produk_toko C
						  ON B.id_produk = C.id_produk_toko
						LEFT JOIN tb_alamat D
						  ON A.id_alamat = D.id_alamat
						LEFT JOIN tb_member E
						  ON A.id_costumer = E.id_member
						WHERE A.id_h_penjualan = '".$id_penjualan."'
						GROUP BY A.id_h_penjualan,C.nama_produk,jumlah,satuan_jual,
							 ongkir,bayar,kode_kurir,kurir,paket,D.detail_alamat,D.kecamatan,kabkota,provinsi,kodepos,sts_penjualan,
							 no_resi,ket_penjualan,isdrop,kode_unik,isGratis,sts_penjualan,bukti_transfer,no_faktur
					) A

			");

			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}



		function get_date($tgl,$range)
		{
			$query = $this->db->query("SELECT DATE_FORMAT(NOW()-INTERVAL '{$range}' DAY,'%d-%m-%Y') AS tgl");

			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}

		function update_status($id_penjualan,$status,$resi)
		{
			$query = "
				UPDATE tb_h_penjualan
				SET status_penjualan = '".$status."'";

			date_default_timezone_set("Asia/Jakarta");
			$date = date('Y-m-d');

			if($status == '2')
			{
				$query .= ", tgl_pengiriman='".$date."',no_resi = '".$resi."'";
			}

			$query .= "	WHERE id_h_penjualan = '".$id_penjualan."'";

			$this->db->query($query);
		}

		function update_h_penjualan($data,$id_h_penjualan)
		{
			$this->db->where('id_h_penjualan',$id_h_penjualan)
					 ->update('tb_h_penjualan',$data);
		}


		function simpan_h_penjualan($data)
		{
			$this->db->insert('tb_h_penjualan',$data);
		}

		function simpan_d_penjualan($data)
		{
			$this->db->insert('tb_d_penjualan',$data);
		}

	  function update_penjualan($data,$id_h_penjualan,$id_costumer)
	  {
	    $this->db->where(array('id_h_penjualan' => $id_h_penjualan,'id_costumer' => $id_costumer))
	             ->update('tb_h_penjualan',$data);
	  }

	  function delete_h_penjualan($data)
	  {
	    $this->db->where($data)
	             ->delete('tb_h_penjualan');
	  }

	  function delete_d_penjualan($data)
	  {
	    $this->db->where($data)
	             ->delete('tb_d_penjualan');
	  }

	}
?>

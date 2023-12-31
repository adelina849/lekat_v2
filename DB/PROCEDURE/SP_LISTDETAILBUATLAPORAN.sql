DROP PROCEDURE IF EXISTS SP_LISTDETAILBUATLAPORAN;

DELIMITER $$
CREATE PROCEDURE `SP_LISTDETAILBUATLAPORAN`(IN `V_DBLAP_KKANTOR` VARCHAR(35), IN `V_LAP_ID` VARCHAR(35), IN `V_BLAP_ID` VARCHAR(35))
BEGIN
DECLARE field2  LONGTEXT;
SET @fields = NULL;
SET @sql = NULL;

CREATE TEMPORARY TABLE IF NOT EXISTS TMP_ILAP_ID AS (SELECT CONCAT('F',RIGHT(ILAP_ID,4)) AS ILAP_ID FROM tb_item_laporan WHERE LAP_ID = V_LAP_ID );

SELECT
  GROUP_CONCAT(DISTINCT
    CONCAT(
      'MAX(IF(ILAP_ID=''',ILAP_ID,''', DBLAP_VALUE,''''))AS ',ILAP_ID
    )
  ) INTO field2
FROM TMP_ILAP_ID; 

SET @sql = CONCAT('   SELECT DBLAP_SEQN, ', field2, ' FROM (SELECT DBLAP_ID,BLAP_ID,LAP_ID,CONCAT(''F'',RIGHT(ILAP_ID,4)) AS ILAP_ID,DBLAP_SEQN,DBLAP_VALUE,DBLAP_KKANTOR FROM tb_d_buat_laporan WHERE BLAP_ID = ''',V_BLAP_ID,''' AND DBLAP_KKANTOR = ''',V_DBLAP_KKANTOR,''') AS A GROUP BY DBLAP_SEQN                ;                   ');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

DROP TABLE TMP_ILAP_ID;
END
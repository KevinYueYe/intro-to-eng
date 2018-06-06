<?php
class Lab_model extends CI_Model {

    public function __construct() {
        $this->load->database();
    }

    public function get_author($author_id) {
        $query = $this->db->get_where('Authors', array('AuthorID' => $author_id));
        return $query->row_array();
    }

    public function get_conference($conference_id) {
        $query = $this->db->get_where('Conferences', array('ConferenceID' => $conference_id));
        return $query->row_array();
    }

    public function get_all() {
        $query = $this->db->get('Authors');
        return $query->result_array();
    }

    public function get_author_total_number($author_name) {
        $target = "\"( |^)".$author_name."\"";
        $command = "SELECT COUNT(*) AS Num FROM Authors WHERE AuthorName REGEXP ($target);";
        $query = $this->db->query($command);
        return $query->row_array()['Num'];
    }

    public function get_affi_total_number($affi_name) {
        $target = "\"( |^)".$affi_name."\"";
        $command = "SELECT COUNT(*) AS Num FROM Affiliations WHERE AffiliationName REGEXP ($target);";
        $query = $this->db->query($command);
        return $query->row_array()['Num'];
    }

    public function get_conference_total_number($conference_name) {
        $target = "\"%".$conference_name."%\"";
        $command = "SELECT COUNT(*) AS Num FROM Conferences WHERE ConferenceName LIKE ($target);";
        $query = $this->db->query($command);
        return $query->row_array()['Num'];
    }

    public function get_paper_total_number($paper_name) {
        $target = "\"( |^)".$paper_name."\"";
        $command = "SELECT COUNT(*) AS Num FROM Papers WHERE Title REGEXP ($target);";
        $query = $this->db->query($command);
        return $query->row_array()['Num'];
    }

    public function search_author($author_name, $begin=null, $num=null) {
        //$target = "\"( |^)".$author_name."( |$)\"";
        $target = "\"( |^)".$author_name."\"";
        if($begin !== null && $num === null) {
            //limit 1 to $begin
            $limit = "limit $begin";
        } else if($begin !==null && $num !==null) {
            //limit $begin to $num
            $limit = "limit $begin, $num";
        } else {
            $limit = "";
        }
        $command = "SELECT a.AuthorID AS AuthorID, a.AuthorName AS AuthorName,COUNT(b.PaperID) AS PaperNum FROM 
        (SELECT * FROM Authors WHERE AuthorName REGEXP ($target) ) a 
        LEFT JOIN PaperAuthorAffiliation b ON a.AuthorID=b.AuthorID 
        GROUP BY AuthorID ORDER BY PaperNum DESC $limit;";
        $query = $this->db->query($command);
        return $query->result_array();
    }

    public function search_affi($affi_name, $begin=null, $num=null) {
        $target = "\"( |^)".$affi_name."\"";
        if($begin !== null && $num === null) {
            //limit 1 to $begin
            $limit = "limit $begin";
        } else if($begin !==null && $num !==null) {
            //limit $begin to $num
            $limit = "limit $begin, $num";
        } else {
            $limit = "";
        }
        $command = "SELECT a.AffiliationID AS AffiliationID, a.AffiliationName AS AffiliationName, COUNT(b.PaperID) AS PaperNum FROM
        (SELECT * FROM Affiliations WHERE AffiliationName REGEXP ($target) ) a
        LEFT JOIN PaperAuthorAffiliation b ON a.AffiliationID=b.AffiliationID
        GROUP BY AffiliationID ORDER BY PaperNum DESC $limit;";
        $query = $this->db->query($command);
        return $query->result_array();
    }

    public function search_conference($conference_name, $begin=null, $num=null) {
        $target = "\"%".$conference_name."%\"";
        if($begin !== null && $num === null) {
            //limit 1 to $begin
            $limit = "limit $begin";
        } else if($begin !==null && $num !==null) {
            //limit $begin to $num
            $limit = "limit $begin, $num";
        } else {
            $limit = "";
        }
        $command = "SELECT a.ConferenceID AS ConferenceID, a.ConferenceName AS ConferenceName, COUNT(b.PaperID) AS PaperNum FROM
        (SELECT * FROM Conferences WHERE ConferenceName LIKE ($target)) a
        LEFT JOIN Papers b ON a.ConferenceID=b.ConferenceID
        GROUP BY ConferenceID ORDER BY PaperNum DESC $limit;";
        $query = $this->db->query($command);
        return $query->result_array();
    }

    public function search_paper($paper_name, $begin=null, $num=null) {
        $target = "\"( |^)".$paper_name."\"";
        if($begin !== null && $num === null) {
            //limit 1 to $begin
            $limit = "limit $begin";
        } else if($begin !==null && $num !==null) {
            //limit $begin to $num
            $limit = "limit $begin, $num";
        } else {
            $limit = "";
        }
        $command="SELECT a.PaperID AS PaperID,
        a.Title AS Title, 
        a.PaperPublishYear AS PaperPublishYear,
        a.ConferenceID AS ConferenceID,
        COUNT(b.ReferenceID) AS ReferenceCount FROM 
        (SELECT * FROM Papers WHERE Title REGEXP ($target) ) a 
        LEFT JOIN PaperReference b ON a.PaperID=b.ReferenceID 
        GROUP BY PaperID ORDER BY ReferenceCount DESC, PaperID ASC $limit;";

        $query = $this->db->query($command);
        return $query->result_array();
    }

    public function get_most_freq_affi($author_id) {
        $target = "\"".$author_id."\"";
        $command = "SELECT a.AffiliationID AS AffiliationID, AffiliationTimes, AffiliationName FROM (SELECT AffiliationID,AffiliationTimes FROM (SELECT AffiliationID, COUNT(AffiliationID) AS AffiliationTimes FROM PaperAuthorAffiliation WHERE AuthorID=$target GROUP BY AffiliationID) a ORDER BY AffiliationTimes DESC LIMIT 1) a INNER JOIN Affiliations b On a.AffiliationID=b.AffiliationID;";
        $query = $this->db->query($command);
        return $query->row_array();
    }

    public function get_author_pub($author_id) {
        $target = "\"".$author_id."\"";
        $command = "SELECT a.PaperID AS PaperID, COUNT(b.ReferenceID) AS ReferenceNum FROM (SELECT PaperID From PaperAuthorAffiliation WHERE AuthorID=$target) a LEFT JOIN PaperReference b ON a.PaperID=b.ReferenceID GROUP BY PaperID ORDER BY ReferenceNum DESC, PaperID;";
        $query = $this->db->query($command);
        return $query->result_array();
    }

    public function get_paper_basic($paper_id) {
        $target = "\"".$paper_id."\"";
        $command = "SELECT Title, ConferenceID, PaperPublishYear FROM Papers WHERE PaperID=$target;";
        $query = $this->db->query($command);
        return $query->row_array();
    }

    public function get_paper_links($paper_id) {
        $target = "\"".$paper_id."\"";
        $command = "SELECT a.AuthorID, AuthorName, AuthorSequence FROM 
            (SELECT AuthorID,AuthorSequence FROM PaperAuthorAffiliation WHERE PaperID=$target) a 
            INNER JOIN Authors b ON a.AuthorID=b.AuthorID ORDER BY AuthorSequence;";
        $query = $this->db->query($command);
        return $query->result_array();
    }

    public function get_cooperation($author_id) {
        $this->db->select('SecondID');
        $this->db->distinct();
        $query = $this->db->get_where('Cooperations', array('FirstID' => $author_id));
        return $query->result_array();
    }

    public function get_relationship($first_id, $second_id) {
        $this->db->select('IsAdvisor');
        $this->db->distinct();
        $query = $this->db->get_where('Cooperations', array('FirstID' => $first_id,
                                                            'SecondID' => $second_id));
        $flag = $query->row_array()['IsAdvisor'];
        return (int) $flag;
    }

    public function is_cooperated($first_id, $second_id) {
        $this->db->distinct();
        $query = $this->db->get_where('CooperationsDup', array('FirstID' => $first_id,
                                                            'SecondID' => $second_id));
        $r = $query->row_array();
        return $r !== NULL;
    }

    public function get_author_of_paper($paper_id) {
        $this->db->where('PaperID', $paper_id);
        $this->db->from('PaperAuthorAffiliation');
        $this->db->join('Authors',
            'PaperAuthorAffiliation.AuthorID = Authors.AuthorID',
            'inner');
        $this->db->select('AuthorName');
        $query = $this->db->get();
        return $query->result_array();
    }
}

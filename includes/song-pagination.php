<?php
    $record_ppage = 6;
    function compute_paging() {
        require 'db-connect.php';
        global $song_ppage;
        $query = "SELECT count(*) FROM songs";
        $result = $conn->query($query);
        $row = $result->fetch_row();
        $p_total = ceil($row[0]/$record_ppage);
        $page = (isset($_REQUEST["page"]))? $_REQUEST["page"] : 1;
        $start = ($page - 1) * $record_ppage;
        $p_prev = ($page > 1)? $page - 1 : 0;
        $p_next = ($page < $p_total)? $page + 1 : 0;
        return array("p_total"=>$p_total, "p_no"=>$page, "p_start"=>$start, "p_prev"=>$p_prev, "p_next"=>$p_next, "total"=>$row[0]);
    }
    function page_nav_links($paging) {
        echo "Page $paging[p_no]/$paging[p_total]:&nbsp&nbsp&nbsp";
        if ($paging['p_prev'] > 0) { //previous
            
            //echo "<a href='title-search-paging.php?search_kw=$search_kw" ."&page=" . $paging['p_prev'] ."'>Previous</a>&nbsp&nbsp&nbsp";
        }
        if ($paging['p_next'] > 0) { //next
            //echo "<a href='title-search-paging.php?search_kw=$search_kw" ."&page=" . $paging['p_next'] ."'>Next</a>";
        }
    }
?>
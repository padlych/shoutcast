<?php
class Application_Model_StyleAmountFont
{
    private $styles;//mixed array 0 => array(size => 16, style => Rock, id => 25)

    public function __construct(array $styles_in, array $map_in)
    {  //map 0 => array(style => 4, amount => 12)
        $fonts = array(16, 22, 28, 33, 44, 56);
        rsort($fonts);
        //exchange amount with size in map
        $tmp_arr = $map_in;
        $i = 0;
        foreach ($tmp_arr as $row) {
            if ( $i < count($fonts) ) {
                $tmp_arr[$i]['amount'] = $fonts[$i];
            }
            else $tmp_arr[$i]['amount'] = 16;
            $i++;
        }
//        die( print_r($tmp_arr) );
        foreach ($styles_in as $style) {
           $id = $style->getId();
           foreach ($tmp_arr as $row) {
               if ( $row['style'] == $id ) {
                   $this->styles[] = array('size' => $row['amount'], 'style' => $style->getStyle(), 'id' => $id);
               }
           }
        }
//        die(print_r($this->styles));
    }

    public function fetchAll()
    {
        return $this->styles;
    }
}
?>
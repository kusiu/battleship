<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Factory\ShipFactory;

class DefaultController extends Controller
{

    const BOARD_WIDTH = 10;
    const BOARD_HEIGHT = 10;

    private $boardWithHead;
    private $boardHeightHead;

    private $ships;
    private $hits;
    private $miss;

    public function __construct()
    {
        $this->boardWithHead = range(1,10);
        $this->boardHeightHead = range('A', 'J');
    }

    /**
     * @Route("/", name="homepage")
     * @Method({"GET"})
     */
    public function indexAction(Request $request)
    {

        $shipsList = array(
            ShipFactory::create('Battleship'),
            ShipFactory::create('Destroyer'),
            ShipFactory::create('Destroyer'),
        );

        $ships = [];

        foreach ($shipsList as $ship) {

            $not_found = true;

            while ($not_found) {

                $coordinates = $ship->generateCoords(self::BOARD_WIDTH, self::BOARD_HEIGHT);
                $ship->setCoordinates($coordinates);

                $overlaps = $this->shipOverlaps($ship, $ships);
                if (!$overlaps) {
                    $not_found = false;
                    $ships[] = $ship;
                }
            }
        }

        $this->setShips($ships);

        $this->saveSetting();

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'outputGrid' => $this->outputGrid()
        ));
    }

    /**
     * @Route("/", name="strike")
     * @Method({"POST"})
     */
    public function strikeAction(Request $request)
    {



        $this->saveSetting();

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'outputGrid' => $this->outputGrid()
        ));
    }

    /**
     * Returns the HTML of the grid table
     *
     */
    public function outputGrid()
    {
        $grid = '<table>';
        $x = 0;
        while ($x <= self::BOARD_HEIGHT) {
            $y = 0;
            $grid .= ($x == 0) ? "<thead><th></th>":"<tr>";
            while ($y <= self::BOARD_WIDTH) {
                if ($x == 0 && $y> 0) {
                    $grid .= "<td>".$this->boardHeightHead[$y-1]."</td>";
                } elseif ($x > 0 && $y == 0) {
                    $grid .= "<th>".$this->boardWithHead[$x-1]."</th>";
                } elseif ($x > 0 && $y > 0) {
                    $grid .= "<td>".$this->outputElem(array($x,$y))."</td>";
                }
                $y++;
            }
            $grid .= ($x == 0) ? "</thead>":"</tr>";
            $x++;
        }
        $grid .= "</table>";
        return $grid;
    }

    /**
     *
     * Helper function to process each point on the grid, and return the
     * correct display
     *
     * @param $coordinate array
     * @return string
     *
     */
    private function outputElem($coordinate)
    {

        foreach ((array) $this->getHits() as $hit_coord) {
            if ($hit_coord === $coordinate) {
                return 'X';
            }
        }
        foreach ((array) $this->getMiss() as $miss_coord) {
            if ($miss_coord === $coordinate) {
                return '-';
            }
        }
        return '.';
    }

    public function saveSetting() {

        $setup = array(
            'ships' => $this->getShipsCoordinates(),
            'hits' => $this->getHits(),
            'miss' => $this->getMiss());

        return setcookie("battleship", json_encode($setup));

    }

    /**
     *  Returns the list of ship's coordinates on the board
     */
    public function getShipsCoordinates()
    {

        $slist = array();
        foreach ($this->ships as $ship) {
            $slist[] = $ship->getCoordinates();
        }
        return $slist;
    }

    public function getHits()
    {
        return $this->hits;
    }

    public function getMiss()
    {
        return $this->miss;
    }

    public function getShips()
    {
        return $this->ships;
    }

    public function setShips($ships)
    {
        $this->ships = $ships;
    }

    /**
     * Checks if a ship overlaps with a list of other ships
     *
     * @param $ship array
     * @param $all_ships array
     * @return boolean
     *
     */
    public function shipOverlaps($ship, $all_ships)
    {
        foreach ($all_ships as $test_s) {
            $overlaps = $test_s->overlapsWith($ship);
            if ($overlaps) {
                return true;
            }
        }
        return false;
    }

}

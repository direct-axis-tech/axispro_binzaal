<?php

class ColumnInfo {
    protected $points = [0,];
    protected $coordinates = [];
    protected $columns = [];
    protected $pageSize = 'A4';
    protected $orientation = 'P';
    protected $margins = [];
    protected $pages = [
        // All mesurements are in 72 ppi and taken from reporting/includes/pdf_report.inc
        // -----------------------------
        // P => width in Portrait mode,
        // L => width in Landscape mode

        "A4" => [
            "P" => 595,
            "L" => 842,
            "margin" => [
                "left" => 40,
                "right" => 30
            ]
        ],
        "A3" => [
            "P" => 842,
            "L" => 1191,
            "margin" => [
                "left" => 50,
                "right" => 40
            ]
        ],
        "A2" => [
            "P" => 1191,
            "L" => 1684,
            "margin" => [
                "left" => 60,
                "right" => 50
            ]
        ],
        "A1" => [
            "P" => 1684,
            "L" => 2384,
            "margin" => [
                "left" => 70,
                "right" => 60
            ]
        ],
        "A0" => [
            "P" => 2384,
            "L" => 3370,
            "margin" => [
                "left" => 60,
                "right" => 50
            ]
        ]
    ];

    public function __construct(
        $columns, 
        $page = 'A4', 
        $orientation = 'P', 
        $margin = []
    )
    {
        $this->columns = $columns;
        $this->pageSize = $page;
        $this->orientation = $orientation;
        $this->margins = $margin;

        $this->calculate();
    }

    public function x1($key)
    {
        return $this->coordinates[$key][0];
    }

    public function x2($key)
    {
        return $this->coordinates[$key][1];
    }

    public function cols()
    {
        return $this->points;
    }

    public function keys()
    {
        return array_keys($this->coordinates);
    }
    
    public function headers()
    {
        return array_column($this->columns, 'title');
    }
    
    public function aligns()
    {
        return array_column($this->columns, 'align');
    }

    protected function calculate() {
        $width = 0;
        $page = $this->pages[$this->pageSize];
        $marginL = $this->margins["left"] ?? $page['margin']['left'];
        $marginR = $this->margins["right"] ?? $page['margin']['right'];
        $maxWidth = $page[$this->orientation] - $marginL - $marginR;
    
        foreach ($this->columns as $key => $col) {
            $width += $col["width"];
            $this->points[] = $width;
            $this->coordinates[$col['key']] = [$key, $key + 1];
        }
    
        $scale = $maxWidth / $width;
        $this->points = array_map(function ($val) use ($scale) {
            return floor($val * $scale);
        }, $this->points);
    }
}
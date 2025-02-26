<?php



class AxisProPagination
{
    protected $baseURL = '';
    protected $totalRows = '';
    protected $perPage = 10;
    protected $numLinks = 2;
    protected $currentPage = 0;
    protected $firstLink = 'First';
    protected $nextLink = 'Next &raquo;';
    protected $prevLink = '&laquo; Prev';
    protected $lastLink = 'Last';
    protected $fullTagOpen = '<div class="pagination">';
    protected $fullTagClose = '</div>';
    protected $firstTagOpen = '';
    protected $firstTagClose = '&nbsp;';
    protected $lastTagOpen = '&nbsp;';
    protected $lastTagClose = '';
    protected $curTagOpen = '&nbsp;<b>';
    protected $curTagClose = '</b>';
    protected $nextTagOpen = '&nbsp;';
    protected $nextTagClose = '&nbsp;';
    protected $prevTagOpen = '&nbsp;';
    protected $prevTagClose = '';
    protected $numTagOpen = '&nbsp;';
    protected $numTagClose = '';
    protected $showCount = true;
    protected $currentOffset = 0;
    protected $queryStringSegment = 'page';

    function __construct($params = array())
    {
        if (count($params) > 0) {
            $this->initialize($params);
        }
    }

    function initialize($params = array())
    {
        if (count($params) > 0) {
            foreach ($params as $key => $val) {
                if (isset($this->$key)) {
                    $this->$key = $val;
                }
            }
        }
    }

    /**
     * Generate the pagination links
     */
    function createLinks()
    {
        // If total number of rows is zero, do not need to continue
        if ($this->totalRows == 0 OR $this->perPage == 0) {
            return '';
        }
        // Calculate the total number of pages
        $numPages = ceil($this->totalRows / $this->perPage);
        // Is there only one page? will not need to continue
        if ($numPages == 1) {
            if ($this->showCount) {
                $info = 'Showing : ' . $this->totalRows;
                return "<span>".$info."</span>";
            } else {
                return '';
            }
        }

        
        /*
         * Decompose and recomposes the url
         * 
         * get all the currently active queries and build the url with place holder
         * for page number assuming the placeholder will not be used in the actual query
         */
        $parsedURL = parse_url($this->baseURL);
        $placeHolder = '{page_no}';
        $urlEncodedPlaceHolder = urlencode($placeHolder);
        $query = [];
        isset($parsedURL['query']) && parse_str($parsedURL['query'], $query);
        $query = array_merge($query, $_GET, ["page" => $placeHolder]);
        $parsedURL['query'] = http_build_query($query);
        $url = makeUrl($parsedURL);

        // Determine the current page
        $this->currentPage = isset($_GET['page']) ? $_GET['page'] : 1;

        if (!is_numeric($this->currentPage) || $this->currentPage == 0) {
            $this->currentPage = 1;
        }

        // Links content string variable
        $output = '';

        // Showing links notification
        if ($this->showCount) {
            $currentOffset = ($this->currentPage > 1) ? ($this->currentPage - 1) * $this->perPage : $this->currentPage;
            $info = 'Showing ' . $currentOffset . ' to ';

            if (($currentOffset + $this->perPage) < $this->totalRows)
                $info .= $this->currentPage * $this->perPage;
            else
                $info .= $this->totalRows;

            $info .= ' of ' . $this->totalRows . ' Rows ';

            $output .= "<span>".$info."</span> ";
        }

        $this->numLinks = (int)$this->numLinks;

        // Is the page number beyond the result range? the last page will show
        if ($this->currentPage > $this->totalRows) {
            $this->currentPage = $numPages;
        }

        $uriPageNum = $this->currentPage;

        // Calculate the start and end numbers.
        $start = (($this->currentPage - $this->numLinks) > 0) ? $this->currentPage - ($this->numLinks - 1) : 1;
        $end = (($this->currentPage + $this->numLinks) < $numPages) ? $this->currentPage + $this->numLinks : $numPages;


        // Render the "First" link
        if ($this->currentPage > $this->numLinks) {
            $output .= $this->firstTagOpen . '&nbsp;<a class="pg-link" href="' . strtr($url, ["$urlEncodedPlaceHolder" => '1']) . '">' . $this->firstLink . '</a>' . $this->firstTagClose;
        }
        // Render the "previous" link
        if ($this->currentPage != 1) {
            $i = ($uriPageNum - 1);
            if ($i == 0) $i = '';
            $output .= $this->prevTagOpen . '<a class="pg-link" href="' . strtr($url, [$urlEncodedPlaceHolder => $i]) . '">' . $this->prevLink . '</a>' . $this->prevTagClose;
        }
        // Write the digit links
        for ($loop = $start - 1; $loop <= $end; $loop++) {
            $i = $loop;
            if ($i >= 1) {


                if ($this->currentPage == $loop) {
                    $output .= $this->curTagOpen . $loop . $this->curTagClose;
                } else {
                    $output .= $this->numTagOpen . '<a class="pg-link" href="' . strtr($url, [$urlEncodedPlaceHolder => $i]) . '">' . $loop . '</a>' . $this->numTagClose;
                }
            }
        }
        // Render the "next" link
        if ($this->currentPage < $numPages) {
            $i = ($this->currentPage + 1);
            $output .= $this->nextTagOpen . '<a class="pg-link" href="' . strtr($url, [$urlEncodedPlaceHolder => $i]) . '">' . $this->nextLink . '</a>' . $this->nextTagClose;
        }
        // Render the "Last" link
        if (($this->currentPage + $this->numLinks) < $numPages) {
            $i = $numPages;
            $output .= $this->lastTagOpen . '<a class="pg-link" href="' . strtr($url, [$urlEncodedPlaceHolder => $i]) . '">' . $this->lastLink . '</a>' . $this->lastTagClose;
        }
        // Remove double slashes
        $output = preg_replace("#([^:])//+#", "\\1/", $output);
        // Add the wrapper HTML if exists
        $output = $this->fullTagOpen . $output . $this->fullTagClose;

        return $output;
    }
}


?>
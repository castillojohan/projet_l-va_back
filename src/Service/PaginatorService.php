<?php

namespace App\Service;

class PaginatorService
{

    /**
     * Builds pagination links and fetches paginated data.
     *
     * @param int $idParameter The identifier parameter for filtering data.
     * @param object $repository The repository object for retrieving data.
     * @param string $path The base path for pagination links.
     *
     * @return array Returns an array containing pagination links and paginated data.
     */
    public function buildPagination($idParameter, $repository, $path)
    {
        $resultByPage = 20;
        $paginationNbr = count($repository->findAll());
        $paginationNbr = ceil($paginationNbr/$resultByPage);

        $paginationLinks = [];
        for($i = 1; $i <= $paginationNbr; $i++){
            $paginationLinks[] = $path.$i;
        }

        $typeOfData = explode("/", $path);
        
        return ['paginationLinks' => $paginationLinks, $typeOfData[3] => $repository->findAllWithPagination($idParameter, $resultByPage)];
    }
}
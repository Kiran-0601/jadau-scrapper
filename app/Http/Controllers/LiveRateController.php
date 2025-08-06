<?php

namespace App\Http\Controllers;

use Spatie\Browsershot\Browsershot;
use Symfony\Component\DomCrawler\Crawler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LiveRateController extends Controller
{
    public function showLiveRates()
    {
        try {
            $html = Browsershot::url('http://narnolicorporation.in')
                ->setOption('args', ['--no-sandbox'])
                ->setOption('timeout', 60000)
                ->setDelay(4000)
                ->noSandbox()
                ->bodyHtml();
            $crawler = new Crawler($html);

            // Extract product rate table
            $productRates = [];
            $crawler->filter('#divProduct .content-cover')->each(function ($node) use (&$productRates) {
                $row = $node->filter('table tbody tr');
                $productName = trim($row->filter('td')->eq(0)->text());
                $mRate = trim($row->filter('td')->eq(1)->text());
                $premium = trim($row->filter('td')->eq(2)->text());
                $sell = trim($row->filter('td')->eq(3)->text());

                $productRates[] = [
                    'product' => $productName,
                    'm_rate' => $mRate,
                    'premium' => $premium,
                    'sell' => $sell,
                ];
            });

            $futureRates = [];
            $crawler->filter('#divFuture .mrt')->each(function ($node) use (&$futureRates) {
                try {
                    $tds = $node->filter('td');
                    if ($tds->count() >= 5) {
                        $futureRates[] = [
                            'product' => trim($tds->eq(0)->text()),
                            'bid' => trim($tds->eq(1)->text()),
                            'ask' => trim($tds->eq(2)->text()),
                            'high' => trim($tds->eq(3)->text()),
                            'low' => trim($tds->eq(4)->text()),
                        ];
                    }
                } catch (\Exception $e) {
                    // skip
                }
            });

            $spotRates = [];
            $crawler->filter('#divSpot .mrt')->each(function ($node) use (&$spotRates) {
                $cells = $node->filter('td');
                $spotRates[] = [
                    'product' => trim($cells->eq(0)->text()),
                    'bid' => trim($cells->eq(1)->text()),
                    'ask' => trim($cells->eq(2)->text()),
                    'high' => trim($cells->eq(3)->text()),
                    'low' => trim($cells->eq(4)->text()),
                ];
            });

            return response()->json([
                'status' => true,
                'productRates' => $productRates,
                'futureRates' => $futureRates,
                'spotRates' => $spotRates,
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()]);
        }
    }
}

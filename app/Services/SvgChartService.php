<?php

namespace App\Services;

use App\Models\Fund;

class SvgChartService
{
    /**
     * Generate SVG charts for a fund
     */
    public function generateChartsForFund(Fund $fund): array
    {
        $charts = [];
        
        if (isset($fund->data['mainContent']['charts'])) {
            $chartData = $fund->data['mainContent']['charts'];
            
            if (isset($chartData['inflationData'])) {
                $charts['inflation'] = $this->generateSimpleInflationChart($chartData['inflationData']);
            }
            
            if (isset($chartData['portfolioData'])) {
                $charts['portfolio'] = $this->generateSimplePortfolioChart($chartData['portfolioData']);
            }
        }
        
        return $charts;
    }
    
    /**
     * Generate placeholder chart when no data is available
     */
    private function generatePlaceholderChart(string $title): string
    {
        $width = 800;
        $height = 400;
        
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="100%" height="100%" fill="#f3f4f6" stroke="#d1d5db" stroke-width="2"/>';
        $svg .= '<text x="' . ($width/2) . '" y="' . ($height/2) . '" text-anchor="middle" font-family="Arial" font-size="18" fill="#6b7280">' . $title . '</text>';
        $svg .= '<text x="' . ($width/2) . '" y="' . ($height/2 + 25) . '" text-anchor="middle" font-family="Arial" font-size="14" fill="#9ca3af">[Chart data not available]</text>';
        $svg .= '</svg>';
        
        return $svg;
    }
    
    /**
     * Generate simple inflation chart with basic shapes only
     */
    private function generateSimpleInflationChart(array $data): string
    {
        if (empty($data)) {
            return $this->generatePlaceholderChart('INVESTMENT STRATEGY VS SA INFLATION');
        }
        
        $width = 800;
        $height = 400;
        
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';
        
        // Title
        $svg .= '<text x="' . ($width/2) . '" y="25" text-anchor="middle" font-family="Arial" font-size="16" font-weight="bold">INVESTMENT STRATEGY VS SA INFLATION</text>';
        
        // Simple chart representation
        $svg .= '<rect x="60" y="60" width="680" height="280" fill="none" stroke="#e0e0e0" stroke-width="1"/>';
        
        // Sample data line (simplified)
        $svg .= '<line x1="60" y1="200" x2="740" y2="120" stroke="#DC2626" stroke-width="3"/>';
        $svg .= '<line x1="60" y1="220" x2="740" y2="220" stroke="#6B7280" stroke-width="2"/>';
        $svg .= '<line x1="60" y1="240" x2="740" y2="240" stroke="#9CA3AF" stroke-width="2"/>';
        
        // Legend
        $svg .= '<circle cx="60" cy="365" r="4" fill="#DC2626"/>';
        $svg .= '<text x="70" y="369" font-family="Arial" font-size="12" fill="#333">Fund Performance</text>';
        $svg .= '<circle cx="200" cy="365" r="4" fill="#6B7280"/>';
        $svg .= '<text x="210" y="369" font-family="Arial" font-size="12" fill="#333">Benchmark</text>';
        $svg .= '<circle cx="320" cy="365" r="4" fill="#9CA3AF"/>';
        $svg .= '<text x="330" y="369" font-family="Arial" font-size="12" fill="#333">Inflation</text>';
        
        $svg .= '</svg>';
        
        return $svg;
    }
    
    /**
     * Generate simple portfolio chart with basic shapes only
     */
    private function generateSimplePortfolioChart(array $data): string
    {
        if (empty($data)) {
            return $this->generatePlaceholderChart('PORTFOLIO PERFORMANCE VS BENCHMARK');
        }
        
        $width = 800;
        $height = 400;
        
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';
        
        // Title
        $svg .= '<text x="' . ($width/2) . '" y="25" text-anchor="middle" font-family="Arial" font-size="16" font-weight="bold">PORTFOLIO PERFORMANCE VS BENCHMARK</text>';
        
        // Simple chart representation
        $svg .= '<rect x="60" y="60" width="680" height="280" fill="none" stroke="#e0e0e0" stroke-width="1"/>';
        
        // Sample data lines (simplified)
        $svg .= '<line x1="60" y1="180" x2="740" y2="80" stroke="#DC2626" stroke-width="3"/>';
        $svg .= '<line x1="60" y1="200" x2="740" y2="100" stroke="#1F2937" stroke-width="2"/>';
        
        // Performance values
        $svg .= '<text x="750" y="84" font-family="Arial" font-size="12" font-weight="bold" fill="#DC2626">R 2,487</text>';
        $svg .= '<text x="750" y="104" font-family="Arial" font-size="12" font-weight="bold" fill="#1F2937">R 2,222</text>';
        
        // Legend
        $svg .= '<circle cx="60" cy="365" r="4" fill="#DC2626"/>';
        $svg .= '<text x="70" y="369" font-family="Arial" font-size="12" fill="#333">Fund</text>';
        $svg .= '<circle cx="180" cy="365" r="4" fill="#1F2937"/>';
        $svg .= '<text x="190" y="369" font-family="Arial" font-size="12" fill="#333">Benchmark</text>';
        
        $svg .= '</svg>';
        
        return $svg;
    }
}
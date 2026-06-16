<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/SearchHistory.php';
require_once __DIR__ . '/../models/Favorite.php';

class ApiController {
    private $db;
    private $searchHistory;
    private $favorite;

    private $providersConfig = [
        '1337x' => [
            'name' => '1337x',
            'slug' => '1337x',
            'credibility' => 'trusted',
            'maintainer' => '1337x Official Team',
            'authorization' => '公开索引，用户自行上传内容，DMCA合规处理',
            'official_url' => 'https://1337x.to',
            'description' => '知名公开种子索引站，内容覆盖面广，审核机制完善'
        ],
        'yts' => [
            'name' => 'YTS',
            'slug' => 'yts',
            'credibility' => 'trusted',
            'maintainer' => 'YTS Team',
            'authorization' => '专注于高清电影资源，遵守DMCA下架流程',
            'official_url' => 'https://yts.mx',
            'description' => '专业电影资源站，以高质量小体积影片著称'
        ],
        'thepiratebay' => [
            'name' => 'The Pirate Bay',
            'slug' => 'thepiratebay',
            'credibility' => 'normal',
            'maintainer' => 'Community Maintained',
            'authorization' => '社区运营，无官方审核，用户需自行甄别',
            'official_url' => 'https://thepiratebay.org',
            'description' => '历史悠久的社区型资源站，内容混杂需谨慎'
        ],
        'rarbg' => [
            'name' => 'Rarbg',
            'slug' => 'rarbg',
            'credibility' => 'trusted',
            'maintainer' => 'RARBG Team',
            'authorization' => '专业团队运营，严格内容审核，DMCA合规',
            'official_url' => 'https://rarbg.to',
            'description' => '高质量资源站，以专业的发布组和严格审核闻名'
        ],
        'torrent9' => [
            'name' => 'Torrent9',
            'slug' => 'torrent9',
            'credibility' => 'pending',
            'maintainer' => 'Unknown',
            'authorization' => '来源可信度待复核，建议谨慎使用',
            'official_url' => 'https://torrent9.to',
            'description' => '法语区资源站，来源可靠性待验证'
        ],
        'eztv' => [
            'name' => 'EZTV',
            'slug' => 'eztv',
            'credibility' => 'normal',
            'maintainer' => 'EZTV Team',
            'authorization' => '专注电视剧资源，社区审核',
            'official_url' => 'https://eztv.re',
            'description' => '老牌电视剧资源站，剧集更新及时'
        ]
    ];

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->searchHistory = new SearchHistory($this->db);
        $this->favorite = new Favorite($this->db);
    }

    public function healthCheck() {
        echo json_encode([
            'status' => 'ok',
            'timestamp' => date('c'),
            'service' => 'Torrent Search API (PHP)'
        ]);
    }

    public function getProviders() {
        $providers = [];
        foreach ($this->providersConfig as $slug => $config) {
            $providers[] = $config;
        }

        $grouped = [
            'trusted' => [],
            'normal' => [],
            'pending' => []
        ];
        foreach ($providers as $p) {
            $grouped[$p['credibility']][] = $p;
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'all' => $providers,
                'grouped' => $grouped,
                'active' => ['1337x', 'Yts']
            ]
        ]);
    }

    public function search($keyword, $query, $page = 1) {
        $results = [];

        if (strtolower($keyword) === 'all') {
            $allSlugs = array_keys($this->providersConfig);
            foreach ($allSlugs as $slug) {
                $this->searchHistory->keyword = $slug;
                $this->searchHistory->query = $query;
                $this->searchHistory->create();
                $per = $this->generateDemoData($query, $slug, $page);
                $results = array_merge($results, $per);
            }
        } else {
            $this->searchHistory->keyword = $keyword;
            $this->searchHistory->query = $query;
            $this->searchHistory->create();
            $results = $this->generateDemoData($query, $keyword, $page);
        }

        $groupedResults = [
            'trusted' => [],
            'normal' => [],
            'pending' => []
        ];
        $delisted = [];

        foreach ($results as $item) {
            if ($item['Status'] === 'delisted' || $item['Status'] === 'violation') {
                $delisted[] = $item;
            } else {
                $groupedResults[$item['Credibility']][] = $item;
            }
        }

        foreach ($groupedResults as $level => &$items) {
            usort($items, function($a, $b) {
                return $b['Seeders'] - $a['Seeders'];
            });
        }
        unset($items);

        $sortedResults = array_merge($groupedResults['trusted'], $groupedResults['normal'], $groupedResults['pending']);

        echo json_encode([
            'success' => true,
            'data' => $sortedResults,
            'grouped' => $groupedResults,
            'delisted_count' => count($delisted),
            'meta' => [
                'keyword' => $keyword,
                'query' => $query,
                'page' => (int)$page,
                'count' => count($sortedResults),
                'trusted_count' => count($groupedResults['trusted']),
                'normal_count' => count($groupedResults['normal']),
                'pending_count' => count($groupedResults['pending']),
                'demo' => true
            ]
        ]);
    }

    public function getHistory() {
        $limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
        $history = $this->searchHistory->findAll($limit);

        echo json_encode([
            'success' => true,
            'data' => $history
        ]);
    }

    public function clearHistory() {
        if ($this->searchHistory->deleteAll()) {
            echo json_encode(['success' => true, 'message' => '搜索历史已清空']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => '清空搜索历史失败']);
        }
    }

    public function addFavorite() {
        $data = json_decode(file_get_contents("php://input"));

        if (!$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
            return;
        }

        if ($this->favorite->findOneByMagnet($data->magnet)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => '该资源已在收藏列表中']);
            return;
        }

        $this->favorite->name = $data->name;
        $this->favorite->magnet = $data->magnet;
        $this->favorite->size = $data->size;
        $this->favorite->seeders = $data->seeders ?? 0;
        $this->favorite->leechers = $data->leechers ?? 0;
        $this->favorite->category = $data->category;
        $this->favorite->source = $data->source;
        $this->favorite->source_name = $data->source_name ?? $data->source;
        $this->favorite->maintainer = $data->maintainer ?? '';
        $this->favorite->authorization = $data->authorization ?? '';
        $this->favorite->mirror_health = $data->mirror_health ?? 'unknown';
        $this->favorite->last_checked_at = $data->last_checked_at ?? null;
        $this->favorite->credibility = $data->credibility ?? 'normal';
        $this->favorite->status = $data->status ?? 'active';
        $this->favorite->delisted_reason = $data->delisted_reason ?? '';

        if ($this->favorite->create()) {
            echo json_encode([
                'success' => true, 
                'message' => '收藏成功', 
                'data' => $this->favorite
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => '收藏失败']);
        }
    }

    public function getFavorites() {
        $favorites = $this->favorite->findAll();
        echo json_encode([
            'success' => true,
            'data' => $favorites
        ]);
    }

    public function deleteFavorite($id) {
        if ($this->favorite->delete($id)) {
            echo json_encode(['success' => true, 'message' => '删除成功']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => '收藏不存在']);
        }
    }

    private function getProviderInfo($providerSlug) {
        $slug = strtolower($providerSlug);
        return $this->providersConfig[$slug] ?? [
            'name' => $providerSlug,
            'slug' => $slug,
            'credibility' => 'pending',
            'maintainer' => 'Unknown',
            'authorization' => '来源信息未知，建议谨慎使用',
            'official_url' => '',
            'description' => '未登记的来源站点'
        ];
    }

    private function getMirrorHealthByCredibility($credibility) {
        $pool = [
            'trusted' => ['healthy', 'healthy', 'healthy', 'healthy', 'healthy', 'warning'],
            'normal'  => ['healthy', 'healthy', 'healthy', 'warning', 'warning', 'unhealthy'],
            'pending' => ['healthy', 'warning', 'warning', 'unhealthy', 'unhealthy', 'unknown']
        ];
        $options = $pool[$credibility] ?? ['unknown'];
        return $options[array_rand($options)];
    }

    private function getLastCheckedByCredibility($credibility) {
        $range = [
            'trusted' => ['min' => 1,   'max' => 24],
            'normal'  => ['min' => 25,  'max' => 168],
            'pending' => ['min' => 169, 'max' => 720]
        ];
        $r = $range[$credibility] ?? ['min' => 1, 'max' => 720];
        $hoursAgo = rand($r['min'], $r['max']);
        return date('c', time() - $hoursAgo * 3600);
    }

    private function getStatusByCredibility($credibility) {
        $pool = [
            'trusted' => ['active', 'active', 'active', 'active', 'active', 'active', 'pending_review', 'delisted'],
            'normal'  => ['active', 'active', 'active', 'active', 'pending_review', 'delisted', 'violation'],
            'pending' => ['active', 'active', 'pending_review', 'pending_review', 'delisted', 'violation']
        ];
        $options = $pool[$credibility] ?? ['active'];
        return $options[array_rand($options)];
    }

    private function generateDemoData($query, $provider, $page) {
        $demoTorrents = [];
        $providerInfo = $this->getProviderInfo($provider);
        $credibility = $providerInfo['credibility'];

        $categories = ['Movies', 'TV', 'Games', 'Software', 'Music'];
        $resolutions = ['1080p', '720p', '2160p', '480p'];

        $count = 10;
        for ($i = 0; $i < $count; $i++) {
            $randomString = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
            $mirrorHealth = $this->getMirrorHealthByCredibility($credibility);
            $status = $this->getStatusByCredibility($credibility);
            $category = $categories[array_rand($categories)];
            $resolution = $resolutions[array_rand($resolutions)];

            $delistedReason = '';
            if ($status === 'delisted') {
                $delistedReason = '链接已失效，无法访问';
            } elseif ($status === 'violation') {
                $delistedReason = '疑似违规内容，已从推荐中移除';
            } elseif ($status === 'pending_review') {
                $delistedReason = '内容待人工复核';
            }

            $lastChecked = $this->getLastCheckedByCredibility($credibility);

            $item = [
                'Name' => "$query Result " . ($i + 1) . " [$provider] [$resolution]",
                'Title' => "$query " . ucfirst($category) . " Release " . ($i + 1),
                'Magnet' => "magnet:?xt=urn:btih:DEMO$randomString&dn=" . urlencode($query),
                'Size' => rand(1, 20) . "." . rand(0, 99) . " GB",
                'Seeders' => rand(50, 2000),
                'Leechers' => rand(10, 500),
                'Category' => $category,
                'Url' => "https://example.com/torrent/" . strtolower(str_replace(' ', '-', $query)) . "-$i",
                'DateUploaded' => rand(1, 30) . ' days ago',
                'Source' => $provider,
                'SourceName' => $providerInfo['name'],
                'Maintainer' => $providerInfo['maintainer'],
                'Authorization' => $providerInfo['authorization'],
                'OfficialUrl' => $providerInfo['official_url'],
                'MirrorHealth' => $mirrorHealth,
                'LastCheckedAt' => $lastChecked,
                'Credibility' => $credibility,
                'Status' => $status,
                'DelistedReason' => $delistedReason
            ];
            $demoTorrents[] = $item;
        }
        return $demoTorrents;
    }
}

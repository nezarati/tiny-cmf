<?
class SiteMap {
	public static function urlset($query) {
		$dom = new DomDocument('1.0', 'UTF-8');
		$urlset = $dom->createElement('urlset');
		$urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		foreach ($query as $row) {
			$url = $dom->createElement('url');
			$url->appendChild($dom->createElement('loc', $row->loc));
			if ($row->lastmod)
				$url->appendChild($dom->createElement('lastmod', date(DateTime::W3C, $row->lastmod)));
			$url->appendChild($dom->createElement('changefreq', $row->changefreq ?: 'never'));
			$url->appendChild($dom->createElement('priority', $row->priority ?: .5));
			$urlset->appendChild($url);
		}
		$dom->appendChild($urlset);
		die($dom->saveXML());
	}
	public static function index($query) {
		$dom = new DomDocument('1.0', 'UTF-8');
		$sitemapindex = $dom->createElement('sitemapindex');
		$sitemapindex->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		foreach ($query as $row) {
			$sitemap = $dom->createElement('sitemap');
			$sitemap->appendChild($dom->createElement('loc', $row->loc));
			$sitemap->appendChild($dom->createElement('lastmod', date(DateTime::W3C, $row->lastmod)));
			$sitemapindex->appendChild($sitemap);
		}
		$dom->appendChild($sitemapindex);
		die($dom->saveXML());
	}
}
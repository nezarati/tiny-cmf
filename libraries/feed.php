<?
class Feed {
	public function atom($query) {
		$dom = new DomDocument('1.0', 'UTF-8');
		
		$feed = $dom->createElement('feed');
		
		$feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
		$feed->setAttribute('xmlns:thr', 'http://purl.org/syndication/thread/1.0');
		$feed->setAttribute('xml:lang', Registry::getInstance('regional', SERVICE_REGIONAL)->language);

		$feed->appendChild($dom->createElement('title', Registry::getInstance()->title));
		$feed->appendChild($dom->createElement('subtitle', Registry::getInstance()->slogan));
		$feed->appendChild($dom->createElement('rights', 'Copyright (c) 2009-2011, Mahdi NezaratiZadeh'));
		
		$generator = $dom->createElement('generator', 'Chonoo Toolkit');
		$generator->setAttribute('uri', 'http://www.chonoo.com');
		$generator->setAttribute('version', '1.0');
		$feed->appendChild($generator);
		
		foreach ($query as $row) {
			$entry = $dom->createElement('entry');
			
			$entry->appendChild($dom->createElement('title', $row->title));
			
			$link = $dom->createElement('link');
			$link->setAttribute('rel', 'alternate');
			$link->setAttribute('type', 'text/html');
			$link->setAttribute('href', $row->url);
			$entry->appendChild($link);
			
			$entry->appendChild($dom->createElement('id', $row->url));
			
			if ($row->modified)
				$entry->appendChild($dom->createElement('updated', date(DateTime::W3C, $row->modified)));
			
			$entry->appendChild($dom->createElement('published', date(DateTime::W3C, $row->created)));
			// <category scheme="http://localhost/wp" term="Uncategorized" />
			
			$summary = $dom->createElement('summary');
			$summary->setAttribute('type', 'html');
			$summary->appendChild($dom->createCDATASection(strip_tags($row->intro)));
			$entry->appendChild($summary);
			
			# TODO
			#$content = $dom->createElement('content');
			#$content->setAttribute('type', 'html');
			#$content->appendChild($dom->createCDATASection($row->content));
			#$entry->appendChild($content);
			
			$feed->appendChild($entry);
			/*<link rel="replies" type="text/html" href="http://localhost/wp/archives/7#comments" thr:count="0"/>
			<link rel="replies" type="application/atom+xml" href="http://localhost/wp/archives/7/feed/atom" thr:count="0"/>
			<thr:total>0</thr:total>*/
		}
		
		$dom->appendChild($feed);
		
		return $dom->saveXML();
	}
}
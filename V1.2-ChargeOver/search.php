<?php
require('lib/init.php');
requireSubscription();

global $userClass, $userDetails;

$countByStateArray = array();

$filter_keyword = '';
$filter_state = isset($_COOKIE['state_id']) ? $_COOKIE['state_id'] : 0;

$view_bookmark = isset($_GET['bookmarks']) ? true : false;
$view_specific_section = isset($_GET['section_id']) ? true : false;

if (!empty($_GET['question_search']) && !$view_bookmark && !$view_specific_section) {
	$filter_keyword = $_GET['keyword'];
	$filter_state = $_GET['state'];
	
	setcookie('state_id', $filter_state, time() + (86400 * 30 * 12), "/");
	$_COOKIE['state_id'] = $filter_state;
	
	$userClass->logAction($userDetails->id, 2, $filter_keyword, $filter_state);
}

$conditionArray = [];
if($filter_keyword != '')
	array_push($conditionArray, ['question', '=', $filter_keyword, PDO::PARAM_STR]);
foreach ($questionClass->countQuestionsByState($conditionArray) as $counter) {
	$countByStateArray[$counter['state_id']] = $counter['count'];
}

include('templates/default/header.php');
?>
<div class="container-fluid content">
    <div class="main-container">
	
		<div class="modal fade" id="shareModal" role="dialog">
			<div class="modal-dialog modal-sm">
			  <div class="modal-content">
				<div class="modal-header">
				  <button type="button" class="close" data-dismiss="modal">&times;</button>
				  <h4 class="modal-title">Share Section</h4>
				</div>
				<div class="modal-body">
				  
					<div class="form-group" style="padding: 15px;">
						<input type="text" name="section_email" class="form-control" placeholder="Enter your email" required>
					</div>
					<p id="shareStatus" style="text-align: center;"></p>
					
				</div>
				<div class="modal-footer">
				  <button type="button" class="btn btn-default" id="sendMailShare">Send</button>
				  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			  </div>
			</div>
		  </div>
		  
		<div class="search-page">
		
		
		<script>
		var bookmarked_ids = <?php echo json_encode($userClass->getBookmarks($userDetails->id)); ?>;
		
		$(function() {
			function refreshBookmarkState() {
				var section_id = $('.result-block.active').find("#answer_data").attr('data-section-id');
				var is_bookmarked = false;
				for(var i = 0; i < bookmarked_ids.length; i++) {
					if(bookmarked_ids[i] == section_id) {
						is_bookmarked = true;
					}
				}
				if(is_bookmarked)
					$('#addBookmark').addClass('bookmarked');
				else
					$('#addBookmark').removeClass('bookmarked');
			}
			
			var btnPrev = $('.btn-prev-result');
			var btnNext = $('.btn-next-result');
				
			$('#shareModal').on('shown', function() {
				$('#shareStatus').text('');
			});
			
			$('#sendMailShare').click(function() {
				$('#shareStatus').text('Sending email...');
				var section_id = $('.result-block.active').find("#answer_data").attr('data-section-id');
				var section_email = $('[name=section_email]').val();
				console.log(section_id);
				console.log(section_email);

				var request = new XMLHttpRequest();
				request.open('GET', 'api/sharesection.php?section_id=' + section_id + '&email=' + section_email, true);
				request.send();
				
				request.onreadystatechange = function(response) {
					if (request.readyState === 4) {
						if (request.status === 200) {
							//var jsonBookmark = JSON.parse(request.responseText);
							//bookmarked_ids = jsonBookmark.bookmarks;
							if(request.responseText == 'success') {
								console.log('success');
								$('#shareStatus').text('Section shared to ' + section_email);
							} else {
								$('#shareStatus').text(request.responseText);
							}
						}
					}
				};
			});
			
			$('#addBookmark').click(function() {
				var section_id = $('.result-block.active').find("#answer_data").attr('data-section-id');
				console.log(section_id);
				
				var request = new XMLHttpRequest();
				request.open('GET', 'api/bookmark.php?action=bookmark&section_id=' + section_id, true);
				request.send();
				
				request.onreadystatechange = function(response) {
					if (request.readyState === 4) {
						if (request.status === 200) {
							var jsonBookmark = JSON.parse(request.responseText);
							bookmarked_ids = jsonBookmark.bookmarks;
							refreshBookmarkState();
							
							/*if(jsonBookmark['bookmark_status'] == 1)
								$('#addBookmark').addClass('bookmarked');
							else
								$('#addBookmark').removeClass('bookmarked');*/
						}
					}
				};
			});
			
			$('.result-block .overlay').click(function() {
				var result_block = $(this).parent();
				var title = result_block.find('.heading').text();
				var paragraph = result_block.find('.line-clamp').html();
				
				//paragraph = paragraph.replace(/ +/g, ' ');
				
				//paragraph = paragraph.replace(/(?:\r\n|\r|\n)/g, '<br />');
				

				
				var content_html = '';
				//content_html += '<a href="viewdocument.php?id=' + result_block.find("#answer_data").attr('data-document-id') + '" target="_blank" class="btn btn-default">View Document</a>';
				content_html += '<h3 class="highlight">' + title + '</h3>';
				content_html += '<div class="paragraph-text" id="paragraph-text">' + paragraph + '</div>';
				
				content_html += '<br /><br /><iframe src="<?php echo BASE_URL; ?>api/document_iframe.php?document_id=' + result_block.find("#answer_data").attr('data-document-id') + '&paragraph_name=' + title + '&page=' + result_block.find("#answer_data").attr('data-page-num') + '" width="100%" height="600px" >';
				
				$('.paragraph-content').html(content_html);
				
				if($(this).parent().hasClass('active'))
					return;
				
				$('.result-block.active').removeClass('active');
				result_block.addClass('active');

				refreshBookmarkState();
				$("#addBookmark").show();				
				$("#shareSection").show();				
				
				if($('.result-block.active').length > 0) {
					if($('.result-block.active').prev().length > 0)
						btnPrev.prop("disabled", false);
					else
						btnPrev.prop("disabled", true);
					
					if($('.result-block.active').next().length > 0)
						btnNext.prop("disabled", false);
					else
						btnNext.prop("disabled", true);
				}
				
				
				
				var log_action = 'section_click';
				var log_question = '<?php echo htmlspecialchars($filter_keyword, ENT_QUOTES, "UTF-8"); ?>';
				var log_state = '<?php echo htmlspecialchars($filter_state, ENT_QUOTES, "UTF-8"); ?>';
				var log_section = title;
				
				var request = new XMLHttpRequest();
				request.open('GET', 'api/userlog.php?action=' + log_action + '&question=' + log_question + "&state=" + log_state + "&section=" + log_section, true);
				request.send();
				
				
				
				<?php if($view_bookmark) { ?>
				var section_id = $('.result-block.active').find("#answer_data").attr('data-section-id');
				console.log(section_id);
				
				var request = new XMLHttpRequest();
				request.open('GET', 'api/bookmark.php?action=highlight_get&section_id=' + section_id, true);
				request.send();
				
				request.onreadystatechange = function(response) {
					if (request.readyState === 4) {
						if (request.status === 200) {
							var jsonHighlight = JSON.parse(request.responseText);
							var posObj = {startOffset: jsonHighlight.highlight_start, endOffset: jsonHighlight.highlight_end};
							
							if(posObj.startOffset == -1 && posObj.endOffset == -1) {
								console.log('no highligh index');
							} else {
								console.log('load highlight at: ' + posObj);
								highlightBookmark(posObj)
							}
						}
					}
				};
				
				$('#paragraph-text').mouseup(function() {
					showCaretPos();
				});
				
				$('#paragraph-text').mousedown(function() {
					$('.paragraph-text').html($('.paragraph-text').html().replace('<mark>', '').replace('</mark>', ''));
				});
				<?php } ?>
			
			});
			
			btnPrev.click(function() {
				$('.result-block.active').prev().find('.overlay').click();
				scrollToActive();
			});
			btnNext.click(function() {
				$('.result-block.active').next().find('.overlay').click();
				scrollToActive();
			});
			
			function scrollToActive() {
				//if(!(result_block.offset().top - $('#searchResults').scrollTop() < $('#searchResults').height()))
					$('#searchResults').scrollTop($('.result-block.active').offset().top - $('.result-block:eq(0)').offset().top);
			}
			
			var input = '<?php echo addslashes($filter_keyword); ?>';
			var inputs = input.split(' ');
			
			function turn_on_highlight() {
				$(".line-clamp").each(function() {
					var paragraph_html = $(this).text();
					var paragraph_words = paragraph_html.split(' ');
					for(var i = 0; i < paragraph_words.length; i++) {
						for(var i2 = 0; i2 < inputs.length; i2++) {
							input_string = inputs[i2].toLowerCase().replace('?', '');
							found_string = paragraph_words[i].toLowerCase();
							if (found_string == input_string) {
								paragraph_words[i] = '<mark>' + paragraph_words[i] + '</mark>';
							}
						}
					}
					paragraph_html = paragraph_words.join(' ');
					$(this).html(paragraph_html);
				});
			}
			
			function turn_off_highlight() {
				$(".line-clamp").each(function() {
					$(this).text($(this).text().replace('<mark>', '').replace('</mark>', ''));
				});
			}
			
			$('#highlight-checkbox').change(function() {
				//console.log(this.checked);
				if (this.checked) {
					turn_on_highlight();
				} else {
					turn_off_highlight();
				}
				$('.result-block.active .overlay').click();
			});
			
			
			
			$("input[type=checkbox]").each(function() {
				var mycookie = $.cookie($(this).attr('name'));
				if (mycookie && mycookie == "true") {
					$(this).prop('checked', mycookie);
					turn_on_highlight();
				}
			});
			
			$("input[type=checkbox]").change(function() {
				$.cookie($(this).attr("name"), $(this).prop('checked'), {
					path: '/',
					expires: 365
				});
			});
			
			var hash_id = location.hash.substr(1) || -1;
			if(hash_id != -1) {
				//$("[data-section-id=" + hash_id + "]").parent().find('.overlay').click();
				//scrollToActive();
			}
			
			//$('.result-block .overlay')[0].click();ubscription
			
			<?php if($view_bookmark) { ?>
			
			function getCaretCharacterOffsetWithin(element) {
				var caretOffset = 0;
				var doc = element.ownerDocument || element.document;
				var win = doc.defaultView || doc.parentWindow;
				var sel;
				if (typeof win.getSelection != "undefined") {
					sel = win.getSelection();
					if (sel.rangeCount > 0) {
						var range = win.getSelection().getRangeAt(0);
						var preCaretRange = range.cloneRange();
						//preCaretRange.selectNodeContents(element);
						preCaretRange.setEnd(range.endContainer, range.endOffset);
						caretOffset = preCaretRange.toString().length;
						return preCaretRange;
					}
				} else if ( (sel = doc.selection) && sel.type != "Control") {
					var textRange = sel.createRange();
					var preCaretTextRange = doc.body.createTextRange();
					preCaretTextRange.moveToElementText(element);
					preCaretTextRange.setEndPoint("EndToEnd", textRange);
					caretOffset = preCaretTextRange.text.length;
				}
				return caretOffset;
			}

			function showCaretPos(posObj) {
				//$('.paragraph-text').html($('.paragraph-text').text());
				//$('.paragraph-text').text($('.paragraph-text').text().replace('<mark>', '').replace('</mark>', ''));
				
				var el = document.getElementById("paragraph-text");
				var posObj = getCaretCharacterOffsetWithin(el);
				
			
				console.log(posObj.startOffset);
				console.log(posObj.endOffset);
				
				if(posObj.startOffset != posObj.endOffset) {
					console.log('saving highlight');
				} else {
					console.log('removing highlight');
				}
								
				var section_id = $('.result-block.active').find("#answer_data").attr('data-section-id');
				var request = new XMLHttpRequest();
				request.open('GET', 'api/bookmark.php?action=highlight_save&section_id=' + section_id + '&start=' + posObj.startOffset + '&end=' + posObj.endOffset, true);
				console.log(posObj);
				request.send();
					
				request.onreadystatechange = function(response) {
					if (request.readyState === 4) {
						if (request.status === 200) {
							var jsonHighlight = JSON.parse(request.responseText);
							if(jsonHighlight.highlight_status == 'success') {
								console.log('successfully saved highlight');
								if(jsonHighlight.start != -1 && jsonHighlight.end != -1)
									highlightBookmark({startOffset: jsonHighlight.start, endOffset: jsonHighlight.end});
							} else if(jsonHighlight.highlight_status == 'fail') {
								console.log('failed to save highlight');
							}
						}
					}
				};
			}
			
			function highlightBookmark(posObj) {
				var original_text = $('.paragraph-text').text();
				var new_text = original_text.splice(posObj.endOffset, 0, '</mark>');
				new_text = new_text.splice(posObj.startOffset, 0, '<mark>');			
					
				$('.paragraph-text').html(new_text);
			}
			
			
			
			String.prototype.splice = function(idx, rem, str) {
				return this.slice(0, idx) + str + this.slice(idx + Math.abs(rem));
			};
			
			<?php } ?>
		});
		</script>
		
		
			
		<?php
		
		$answers = [];
		if($view_specific_section) {
			$section_id = $_GET['section_id'];
			$answers_solr = $searchClass->search(
				'*',
				'entity_type:1 AND document_type:3 AND id:' . $section_id,
				'',
				1
			);
			$answers = $answers_solr;
		} else if($view_bookmark) {
			$bookmarks = $userClass->getBookmarks($userDetails->id);
			if(sizeof($bookmarks) > 0) {
				$answers_solr = $searchClass->search(
					'*',
					'entity_type:1 AND document_type:3 AND id:(' . implode(' ', $bookmarks) . ')',
					'paragraph_num^4 paragraph_title paragraph_text',
					25
				);
				$answers = $answers_solr;
			}
		} else if(strlen($filter_keyword) >= 3) {
			$answers_solr = $searchClass->search(
				$filter_keyword . '*',
				'entity_type:1 AND document_type:3' . ($filter_state > 0 ? ' AND state_id:' . $filter_state : ''),
				'paragraph_num^4 paragraph_title paragraph_text',
				25
			);
			$answers = $answers_solr;
			

			
			$suggested_question = $questionClass->findQuestion(0, $filter_keyword);
			if($suggested_question) {
				$suggested_answer = $searchClass->search(
					'*',
					'entity_type:1 AND document_type:3 AND paragraph_num:' . $suggested_question->paragraph_num . ($filter_state > 0 ? ' AND state_id:' . $filter_state : ''),
					'',
					1
				);
				if(sizeof($suggested_answer) > 0) {
					$suggested_answer[0]['suggested'] = true;
					array_unshift($answers, $suggested_answer[0]);
				}
			}
			
			//print_r($answers);
			
			foreach($answers as $answerKey => $answerVal) {
				//echo('|||' . $answerVal['paragraph_num'] . $answerVal['paragraph_title'] . '|||');
				if(preg_match('/([0-9])+([X\s])+([0-9])+([\.])+/m', $answerVal['paragraph_num'] . $answerVal['paragraph_title']) ||
					preg_match('/([0-9])+(\(Reserved\))+(\s)+([0-9])+([\.])+/m', $answerVal['paragraph_num'] . $answerVal['paragraph_title']) ||
					trim(preg_replace('/\s\s+/', ' ', $answerVal['paragraph_text'])) == '') {
					unset($answers[$answerKey]);
				}
			}
			
			$ids = array_column($answers, 'paragraph_num');
			$ids = array_unique($ids);
			$array = array_filter($answers, function ($key, $value) use ($ids) {
				return in_array($value, array_keys($ids));
			}, ARRAY_FILTER_USE_BOTH);
			
			$answers = $array;
		}
		
		/*for($i = 0; $i < sizeof($answers); $i++) {
			$answer_words = explode(' ', $answers[$i]['paragraph_text']);
			for($i2 = 0; $i2 < sizeof($answer_words); $i2++) {
				$filter_keyword_words = explode(' ', $filter_keyword);
				foreach($filter_keyword_words as $filter_keyword_word) {
					$filter_keyword_word = str_replace('?', '', $filter_keyword_word);
					if(strlen($filter_keyword_word) < 2)
						continue;
					if (stripos($answer_words[$i2], $filter_keyword_word) !== false) {
						$answer_words[$i2] = '<mark>' . $answer_words[$i2] . '</mark>';
						continue;
					}
				}
			}
			//$answers[$i]['paragraph_text'] = implode(' ', $answer_words);
		}*/
		
		//$answers = $searchClass->search($filter_keyword, $filter_state);
				
		echo	'<div class="rate-panel clearfix">';
		echo 		'<div class="row">';
		echo 			'<div class="col-md-12">';
		echo 				'<div class="panel-actions">';
		echo 					'<div class="col-md-4 col-sm-4 block-action-heading">';
		if($view_bookmark) {
				if(sizeof($answers) > 0)
					echo 					'<div class="mobile-sketcher-changer"><span>&lt;</span><a>Bookmarks</a>&nbsp;&nbsp;&nbsp;</div>';
				else
					echo 					'<div class="mobile-sketcher-changer"><span>&lt;</span><a href="javascript:void(0)">No Bookmarks</a></div>';		
		} else if(sizeof($answers) > 0) {
				echo 					'<div class="mobile-sketcher-changer"><span>&lt;</span><a>Search Results</a>&nbsp;&nbsp;&nbsp;</div>';
				echo 					'<div class="mobile-sketcher-changer"><span>&lt;</span><a>Highlight keywords</a>&nbsp;&nbsp;&nbsp;
											<div class="material-switch pull-right">
												<input id="highlight-checkbox" name="highlight-checkbox" type="checkbox"/>
												<label for="highlight-checkbox" class="label-success"></label>
											</div>
										</div>';
				echo 					'<span class="sorting">Sorted By relevance</span>';
		} else {
			if($filter_keyword!=''){
echo 					'<div class="mobile-sketcher-changer"><span>&lt;</span><a href="javascript:void(0)">No Results</a></div>';	
				
			}
					
		}
		echo 					'</div>';
		if(sizeof($answers) > 0) {
			echo '<div class="col-md-3 col-sm-2 col-xs-3 action-value text-right">
					<ul class="nav-menu rounded" style="float: right !important;">       
					  <li>
						<a title="Share" id="shareSection" style="display: none;" data-toggle="modal" data-target="#shareModal">
						  <i class="icon share"></i>
						  <span class="hidden-sm">Share</span>
						</a>
					  </li>
					  <li>
						<a title="Bookmark" id="addBookmark" style="display: none;">
						  <i class="icon bookmarks"></i>
						  <span class="hidden-sm">Bookmark</span>
						</a>
					  </li>
					</ul>
				  </div>';  
		}
		
		if(sizeof($answers) > 0) {
			//echo 					'<div class="col-md-3 col-sm-3"></div>';
			echo 					'<div class="col-md-3 col-sm-3"></div>';
			echo 					'<div class="col-md-2 col-sm-2">';
			echo 						'<button class="btn btn-default btn-xs btn-prev-result" disabled>Previous</button>';
			echo						'&nbsp;&nbsp;&nbsp;';
			echo 						'<button class="btn btn-default btn-xs btn-next-result" disabled>Next</button>';
			echo 					'</div>';
			//echo 					'<div class="col-md-3 col-sm-3"></div>';
		}
				
		echo 				'</div>';
		echo 			'</div>';
		echo 		'</div>';
		echo 	'</div>';
				
				
				
		if(sizeof($answers) > 0) {
			echo '<div class="row search-results mobile-sketcher">';
			echo '<div id="searchResults" class="col-sm-4 col-md-4 results-list mobile-sketcher-screen active">';
			$answer_num = 0;
			foreach($answers as $answer) {
				//echo '---' . $answer['document_id'] . '---';
				//echo $answer['id'];
				$answer_num++;
				echo	'<div class="result-block faded">';
				echo 		'<div class="heading">' . $answer['paragraph_num'] .'  ' . $answer['paragraph_title'] . '</div>';
				if($answer_num == 1 && !$view_bookmark && !$view_specific_section)
					echo 	'<div class="highlight">BEST MATCH' . (isset($answer['suggested']) ? '(SUGGESTED)' : '') . '</div>';
				echo 		'<p class="line-clamp">' . $answer['paragraph_text'] . '</p>';
				echo		'<div class="overlay"></div>';
				if(!isset($answer['page_num']))
					$answer['page_num'] = 0;
				echo		'<input type="hidden" id="answer_data" data-document-id="' . $answer['document_id'] . '" data-page-num="' . $answer['page_num'] . '" data-section-id="' . $answer['id'] . '">';
				echo 	'</div>';
			}
			echo '</div>';
			
			echo '<div class="col-md-offset-4 col-md-8 col-sm-offset-4 col-sm-8 result-item results-list mobile-sketcher-screen">';
			echo '	<div id="searchedChapter" ng-mouseup="showPopup($event)">';
			echo '		<div class="col-md-12">';
			echo '			<p class="paragraph-content"></p>';			
			echo '		</div>';
			echo '	</div>';
			echo '</div>';
			echo '</div>';
		}
		?>
				
				
		
		</div>
	</div>
</div>
<?php include('templates/default/footer.php'); ?>
function mdWrap(editorId, before, after) {
  const editor = document.getElementById(editorId);
  const start = editor.selectionStart;
  const end = editor.selectionEnd;
  const scrollTop = editor.scrollTop; // Save scroll position
  const text = editor.value;
  const selected = text.substring(start, end);
  editor.value = text.substring(0, start) + before + selected + after + text.substring(end);
  editor.focus({ preventScroll: true }); // Prevent scroll on focus
  editor.scrollTop = scrollTop; // Restore scroll position
  editor.setSelectionRange(start + before.length, end + before.length);
}

function mdInsert(editorId, text) {
  const editor = document.getElementById(editorId);
  const start = editor.selectionStart;
  const scrollTop = editor.scrollTop; // Save scroll position
  const val = editor.value;
  editor.value = val.substring(0, start) + text + val.substring(start);
  editor.setSelectionRange(start + text.length, start + text.length);
  editor.scrollTop = scrollTop; // Restore scroll position BEFORE focus
  editor.focus({ preventScroll: true }); // Prevent scroll on focus
}

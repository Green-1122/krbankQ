</div><!-- /page-content -->
</div><!-- /main-content -->
</div><!-- /app-layout -->
<script>
// Global JS helpers
function showModal(id){document.getElementById(id).classList.add('active')}
function hideModal(id){document.getElementById(id).classList.remove('active')}
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('active')})});
</script>
</body></html>

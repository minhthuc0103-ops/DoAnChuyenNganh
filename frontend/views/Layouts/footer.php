</div><!-- end .page-content -->
</div><!-- end .main -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
<script src="frontend/assets/js/layouts-footer.js"></script>
<script src="frontend/assets/js/layouts-footer-2.js"></script>
<script src="frontend/assets/js/layouts-footer-3.js"></script>

<!-- ═══ AI CHATBOX ═══ -->
<link rel="stylesheet" href="frontend/assets/css/layouts-footer.css">

<?php if (($_GET['controller'] ?? '') !== 'chat'): ?>
<button class="ai-fab" id="aiFab" onclick="toggleAI()" title="Chat AI">🤖</button>
<?php endif; ?>

<div class="ai-box" id="aiBox">
  <div class="ai-head">
    <div class="ai-head-title">🤖 RoomManager AI</div>
    <div class="ai-head-actions">
      <button onclick="clearAI()" title="Xóa lịch sử">🗑</button>
      <button onclick="toggleAI()">✕</button>
    </div>
  </div>

  <div class="ai-messages" id="aiMessages">
    <div class="ai-msg bot">
      <div class="ai-bubble">Xin chào! Tôi là trợ lý AI của RoomManager 🏠<br><br>Bạn có thể hỏi tôi về:<br>• Quản lý phòng, hợp đồng<br>• Tính tiền điện nước<br>• Nội quy khu trọ<br>• Quy trình thuê/trả phòng</div>
    </div>
    <!-- Gợi ý nhanh -->
    <div class="ai-suggestions" id="aiSuggestions">
      <?php if (($_SESSION['vai_tro'] ?? '') === 'user'): ?>
      <button onclick="askSuggestion('Phòng trống')">🏠 Phòng trống</button>
      <button onclick="askSuggestion('Công nợ')">💰 Công nợ</button>
      <button onclick="askSuggestion('Nội quy')">📋 Nội quy</button>
      <button onclick="askSuggestion('Giá phòng')">💵 Giá phòng</button>
      <button onclick="askSuggestion('Đơn giá điện nước')">⚡ Đơn giá</button>
      <button onclick="askSuggestion('Khu trọ')">🏘 Khu trọ</button>
      <?php else: ?>
      <button onclick="askSuggestion('Phòng trống')">🏠 Phòng trống</button>
      <button onclick="askSuggestion('Công nợ')">⚠ Công nợ</button>
      <button onclick="askSuggestion('Doanh thu')">💰 Doanh thu</button>
      <button onclick="askSuggestion('Hợp đồng sắp hết hạn')">📄 HĐ sắp hết</button>
      <button onclick="askSuggestion('Hóa đơn')">⚡ Hóa đơn</button>
      <button onclick="askSuggestion('Người thuê')">👥 Người thuê</button>
      <button onclick="askSuggestion('Giá phòng')">💵 Giá phòng</button>
      <button onclick="askSuggestion('Khu trọ')">🏘 Khu trọ</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="ai-foot">
    <div class="ai-row">
      <textarea id="aiInput" placeholder="Nhập câu hỏi..." rows="1"></textarea>
      <button id="aiSendBtn" onclick="sendAI()">Gửi</button>
    </div>
  </div>
</div>

<script src="frontend/assets/js/layouts-footer-4.js"></script>

<link rel="stylesheet" href="frontend/assets/css/layouts-footer-2.css">

<div class="app-confirm-backdrop" id="appConfirmModal" aria-hidden="true">
  <div class="app-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="appConfirmTitle">
    <div class="app-confirm-head">
      <div class="app-confirm-icon">!</div>
      <div class="app-confirm-title" id="appConfirmTitle">Xác nhận thao tác</div>
    </div>
    <div class="app-confirm-message" id="appConfirmMessage">Bạn có chắc muốn thực hiện thao tác này?</div>
    <div class="app-confirm-actions">
      <button type="button" class="btn btn-outline" id="appConfirmCancel">Hủy</button>
      <button type="button" class="btn btn-danger" id="appConfirmOk">Đồng ý</button>
    </div>
  </div>
</div>

</body>
</html>

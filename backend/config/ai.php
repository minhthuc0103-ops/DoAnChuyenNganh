<?php
/**
 * Cấu hình AI Chat
 * Mode: 'local' = tự trả lời bằng logic PHP (không cần API key)
 *       'gemini' = dùng Google Gemini API
 *       'openai' = dùng OpenAI API
 */

define('AI_PROVIDER', 'local'); // 'local' = không cần API, tự xử lý

// Claude / Anthropic (nếu muốn dùng sau)
define('CLAUDE_API_KEY', '');
define('CLAUDE_MODEL', 'claude-haiku-4-5-20251001');

// Google Gemini (nếu muốn dùng sau)
define('GEMINI_API_KEY', '');
define('GEMINI_MODEL', 'gemini-2.0-flash');

// OpenAI (nếu muốn dùng sau)
define('OPENAI_API_KEY', '');
define('OPENAI_MODEL', 'gpt-3.5-turbo');

// System prompt (dùng cho API mode)
define('AI_SYSTEM_PROMPT', 'Bạn là trợ lý AI của hệ thống quản lý phòng trọ RoomManager.');

-- Tabela para armazenar tokens de API
CREATE TABLE IF NOT EXISTS api_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(64) NOT NULL,
  description VARCHAR(255) NOT NULL,
  user_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP NULL,
  is_active BOOLEAN DEFAULT TRUE,
  FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  UNIQUE KEY (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir um token padrão para teste
INSERT INTO api_tokens (token, description) 
VALUES ('seu_token_de_acesso', 'Token de acesso padrão para testes');

-- Tabela para armazenar logs de acesso à API
CREATE TABLE IF NOT EXISTS api_access_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(64) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  user_agent VARCHAR(255),
  endpoint VARCHAR(100) NOT NULL,
  access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  response_code INT,
  FOREIGN KEY (token) REFERENCES api_tokens(token) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 
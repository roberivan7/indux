-- ============================================================
-- INDUX — banco.sql
-- Banco de dados MySQL completo
-- Execute: mysql -u root -p < banco.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS indux
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE indux;

-- ── Usuários ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome         VARCHAR(150) NOT NULL,
  email        VARCHAR(200) NOT NULL UNIQUE,
  senha        VARCHAR(255) NOT NULL,
  perfil       ENUM('admin','funcionario') NOT NULL DEFAULT 'funcionario',
  ativo        TINYINT(1) NOT NULL DEFAULT 1,
  ultimo_acesso DATETIME NULL,
  criado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- Permissões granulares para funcionários
  is_operador          TINYINT(1) DEFAULT 0,
  perm_criar_equip     TINYINT(1) DEFAULT 0,
  perm_editar_equip    TINYINT(1) DEFAULT 0,
  perm_resolver_alarme TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Equipamentos ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS equipamentos (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tag          VARCHAR(50)  NOT NULL UNIQUE,
  nome         VARCHAR(150) NOT NULL,
  modelo       VARCHAR(100) NULL,
  fabricante   VARCHAR(100) NULL,
  localizacao  VARCHAR(200) NULL,
  descricao    TEXT NULL,
  status       ENUM('ativo','inativo','em_falha') NOT NULL DEFAULT 'ativo',
  temp_min     FLOAT NOT NULL DEFAULT 0,
  temp_max     FLOAT NOT NULL DEFAULT 80,
  pressao_min  FLOAT NOT NULL DEFAULT 0,
  pressao_max  FLOAT NOT NULL DEFAULT 10,
  criado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Leituras de sensor ────────────────────────────────────
CREATE TABLE IF NOT EXISTS leituras_sensor (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  equipamento_id  INT UNSIGNED NOT NULL,
  temperatura     FLOAT NOT NULL,
  pressao         FLOAT NOT NULL,
  umidade         FLOAT NULL,
  registrado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON DELETE CASCADE,
  INDEX idx_equip_data (equipamento_id, registrado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Alarmes ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS alarmes (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  equipamento_id   INT UNSIGNED NOT NULL,
  tipo             ENUM('temperatura','pressao','falha','conexao','manutencao') NOT NULL,
  severidade       ENUM('critico','alerta','informativo') NOT NULL DEFAULT 'alerta',
  mensagem         TEXT NOT NULL,
  valor_registrado FLOAT NULL,
  valor_limite     FLOAT NULL,
  resolvido        TINYINT(1) NOT NULL DEFAULT 0,
  resolvido_por    INT UNSIGNED NULL,
  resolvido_em     DATETIME NULL,
  criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON DELETE CASCADE,
  FOREIGN KEY (resolvido_por)  REFERENCES usuarios(id)     ON DELETE SET NULL,
  INDEX idx_equip_status (equipamento_id, resolvido),
  INDEX idx_severidade   (severidade, resolvido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Log de sistema ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS log_sistema (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id      INT UNSIGNED NULL,
  acao            VARCHAR(100) NOT NULL,
  tabela_afetada  VARCHAR(100) NULL,
  registro_id     INT UNSIGNED NULL,
  detalhes        TEXT NULL,
  ip              VARCHAR(45) NULL,
  criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX idx_acao (acao),
  INDEX idx_data (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

-- Admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha, perfil) VALUES
('Administrador INDUX', 'admin@indux.com.br',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id=id;

-- Equipamentos de exemplo
INSERT INTO equipamentos (tag, nome, modelo, fabricante, localizacao, status, temp_min, temp_max, pressao_min, pressao_max) VALUES
('CLD-001', 'Caldeira Principal',    'CBR-5000',    'ThermoTec',  'Sala 01',    'ativo',   20, 95, 0, 15),
('CMP-002', 'Compressor Industrial', 'AIR-2400',    'PneumaCorp', 'Área B',     'em_falha',15, 70, 0, 12),
('BBA-003', 'Bomba Hidráulica',      'HYD-800',     'FluidTech',  'Subsolo',    'ativo',   10, 65, 1, 10),
('TRF-004', 'Transformador Elétrico','TRF-500KVA',  'ElectraInd', 'Subestação', 'inativo',  0, 85, 0,  5)
ON DUPLICATE KEY UPDATE id=id;

-- Leituras de exemplo
INSERT INTO leituras_sensor (equipamento_id, temperatura, pressao, umidade, registrado_em) VALUES
(1, 72.4, 8.1,  55.0, NOW() - INTERVAL 2  MINUTE),
(1, 70.1, 7.9,  54.0, NOW() - INTERVAL 7  MINUTE),
(1, 68.5, 7.5,  53.0, NOW() - INTERVAL 12 MINUTE),
(2, 88.9, 13.2, NULL, NOW() - INTERVAL 1  MINUTE),
(2, 85.2, 12.8, NULL, NOW() - INTERVAL 6  MINUTE),
(3, 45.0, 6.5,  NULL, NOW() - INTERVAL 5  MINUTE),
(3, 44.2, 6.3,  NULL, NOW() - INTERVAL 10 MINUTE);

-- Alarmes de exemplo
INSERT INTO alarmes (equipamento_id, tipo, severidade, mensagem, valor_registrado, valor_limite) VALUES
(2, 'temperatura', 'critico', 'Temperatura acima do limite: 88.9°C (máx: 70°C)', 88.9, 70),
(2, 'pressao',     'critico', 'Pressão acima do limite crítico: 13.2 bar (máx: 12 bar)', 13.2, 12),
(1, 'manutencao',  'alerta',  'Manutenção preventiva programada para esta semana', NULL, NULL);

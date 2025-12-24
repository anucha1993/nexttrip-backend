-- Add api_id mapping for iTravel
INSERT INTO tb_tour_api_field_mappings (provider_id, target_table, target_field, source_field, created_at, updated_at)
SELECT id, 'tours', 'api_id', 'code', NOW(), NOW()
FROM tb_tour_api_providers
WHERE code = 'itravels'
AND NOT EXISTS (
    SELECT 1 FROM tb_tour_api_field_mappings m
    WHERE m.provider_id = tb_tour_api_providers.id
    AND m.target_table = 'tours'
    AND m.target_field = 'api_id'
);

-- Verify
SELECT target_field, source_field 
FROM tb_tour_api_field_mappings 
WHERE provider_id = (SELECT id FROM tb_tour_api_providers WHERE code = 'itravels')
AND target_table = 'tours'
ORDER BY target_field;

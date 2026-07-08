import React, { useState, useEffect } from 'react';
import { StyleSheet, View, Text, ScrollView, TouchableOpacity, ActivityIndicator, Alert, TextInput } from 'react-native';

export default function RunScreen({ userToken, serverUrl }) {
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  
  // Selected project and triggers state
  const [selectedProject, setSelectedProject] = useState(null);
  const [customKeyword, setCustomKeyword] = useState('');
  const [customTargetUrl, setCustomTargetUrl] = useState('');
  const [platform, setPlatform] = useState(''); // empty for ALL, or name of specific platform
  
  const [submitting, setSubmitting] = useState(false);
  const [runResults, setRunResults] = useState(null);

  const fetchProjects = async () => {
    try {
      const response = await fetch(`${serverUrl}/api/projects.php`, {
        method: 'GET',
        headers: {
          'Authorization': `Bearer ${userToken}`,
          'Content-Type': 'application/json',
        },
      });
      const data = await response.json();
      if (response.status === 200 && data.success) {
        setProjects(data.projects);
      }
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchProjects();
  }, []);

  const selectProject = (project) => {
    setSelectedProject(project);
    // Pre-fill fields
    const keywords = arrayFromCommaString(project.target_keyword);
    const sites = arrayFromCommaString(project.target_site || project.website_url);
    
    setCustomKeyword(keywords[0] || '');
    setCustomTargetUrl(sites[0] || '');
    setRunResults(null);
  };

  const arrayFromCommaString = (str) => {
    if (!str) return [];
    return str.split(',').map(s => s.trim()).filter(Boolean);
  };

  const handleTriggerRun = async () => {
    if (!selectedProject) {
      Alert.alert('Error', 'Please select a project first.');
      return;
    }

    setSubmitting(true);
    setRunResults(null);
    try {
      const response = await fetch(`${serverUrl}/api/run.php`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${userToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          project_id: selectedProject.id,
          keyword: customKeyword.trim(),
          target_site: customTargetUrl.trim(),
          platform: platform.trim(),
        }),
      });

      const data = await response.json();

      if (response.status === 200 && data.success) {
        setRunResults(data);
        Alert.alert('Trigger Successful', `Queued posting tasks.`);
      } else {
        Alert.alert('Execution Failed', data.error || 'Failed to trigger postings.');
      }
    } catch (error) {
      console.error(error);
      Alert.alert('Connection Error', 'Could not execute trigger on the server.');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.center}>
        <ActivityIndicator size="large" color="#4f46e5" />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.headerTitle}>Run SEO Postings</Text>
          <Text style={styles.headerSub}>Trigger Backlink Engines</Text>
        </View>
      </View>

      <ScrollView contentContainerStyle={styles.scrollContent}>
        {/* Project Selector Card */}
        <Text style={styles.sectionLabel}>Select Target Project</Text>
        <View style={styles.selectorGrid}>
          {projects.map((proj) => (
            <TouchableOpacity
              key={proj.id}
              style={[
                styles.projectSelectBtn,
                selectedProject?.id === proj.id && styles.projectSelectBtnActive
              ]}
              onPress={() => selectProject(proj)}
            >
              <Text
                style={[
                  styles.selectBtnText,
                  selectedProject?.id === proj.id && styles.selectBtnTextActive
                ]}
                numberOfLines={1}
              >
                {proj.business_name || proj.website_url}
              </Text>
            </TouchableOpacity>
          ))}
          {projects.length === 0 && (
            <Text style={styles.noProjText}>No projects found. Add projects first.</Text>
          )}
        </View>

        {selectedProject && (
          <View style={styles.runForm}>
            {/* Keyword Input */}
            <View style={styles.inputGroup}>
              <Text style={styles.label}>Backlink Keyword (Anchor Text)</Text>
              <TextInput
                style={styles.input}
                placeholder="Anchor text"
                value={customKeyword}
                onChangeText={setCustomKeyword}
              />
            </View>

            {/* Target URL Input */}
            <View style={styles.inputGroup}>
              <Text style={styles.label}>Target Landing Page URL</Text>
              <TextInput
                style={styles.input}
                placeholder="URL to build backlink to"
                value={customTargetUrl}
                onChangeText={setCustomTargetUrl}
                autoCapitalize="none"
                autoCorrect={false}
              />
            </View>

            {/* Platform Filter Selector */}
            <View style={styles.inputGroup}>
              <Text style={styles.label}>Target Platform (Optional)</Text>
              <View style={styles.platformButtons}>
                {[
                  { name: 'All Platforms', value: '' },
                  { name: 'Tumblr', value: 'tumblr' },
                  { name: 'LiveJournal', value: 'livejournal' },
                  { name: 'Symbaloo', value: 'symbaloo' },
                ].map((item) => (
                  <TouchableOpacity
                    key={item.name}
                    style={[
                      styles.platformBtn,
                      platform === item.value && styles.platformBtnActive
                    ]}
                    onPress={() => setPlatform(item.value)}
                  >
                    <Text
                      style={[
                        styles.platformBtnText,
                        platform === item.value && styles.platformBtnTextActive
                      ]}
                    >
                      {item.name}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>
            </View>

            {/* Submit Button */}
            <TouchableOpacity
              style={[styles.submitBtn, submitting && styles.submitBtnDisabled]}
              onPress={handleTriggerRun}
              disabled={submitting}
            >
              <Text style={styles.submitBtnText}>
                {submitting ? 'Triggering bots...' : '🚀 Submit Posting Run'}
              </Text>
            </TouchableOpacity>

            {/* Results Report Card */}
            {runResults && (
              <View style={styles.resultsCard}>
                <Text style={styles.resultsTitle}>Run Queue Status</Text>
                <Text style={styles.resultsMsg}>
                  Queued: {runResults.queued_count} tasks added.
                </Text>
                
                <View style={styles.resultsList}>
                  {runResults.results?.map((res, index) => (
                    <View key={index} style={styles.resRow}>
                      <Text style={styles.resPlatform}>
                        {res.platform.toUpperCase()} ({res.username})
                      </Text>
                      <View
                        style={[
                          styles.resStatusBadge,
                          res.status === 'queued' ? styles.queuedBadge : styles.dupBadge
                        ]}
                      >
                        <Text style={styles.resStatusText}>{res.status}</Text>
                      </View>
                    </View>
                  ))}
                </View>
              </View>
            )}
          </View>
        )}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f8fafc',
  },
  center: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#f8fafc',
  },
  header: {
    paddingHorizontal: 24,
    paddingTop: 20,
    paddingBottom: 16,
    backgroundColor: '#ffffff',
    borderBottomWidth: 1,
    borderBottomColor: '#e2e8f0',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: '800',
    color: '#0f172a',
  },
  headerSub: {
    fontSize: 12,
    color: '#64748b',
    fontWeight: '600',
  },
  scrollContent: {
    padding: 20,
  },
  sectionLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: '#475569',
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  selectorGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: 20,
  },
  projectSelectBtn: {
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    paddingVertical: 10,
    paddingHorizontal: 14,
    minWidth: '47%',
    flex: 1,
  },
  projectSelectBtnActive: {
    backgroundColor: '#e0e7ff',
    borderColor: '#4f46e5',
  },
  selectBtnText: {
    fontSize: 13,
    color: '#475569',
    fontWeight: '600',
    textAlign: 'center',
  },
  selectBtnTextActive: {
    color: '#4f46e5',
  },
  noProjText: {
    color: '#64748b',
    fontSize: 13,
  },
  runForm: {
    marginTop: 8,
  },
  inputGroup: {
    marginBottom: 16,
  },
  label: {
    fontSize: 12,
    fontWeight: '700',
    color: '#475569',
    marginBottom: 6,
  },
  input: {
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 14,
    color: '#0f172a',
  },
  platformButtons: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  platformBtn: {
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 6,
    paddingVertical: 8,
    paddingHorizontal: 12,
    backgroundColor: '#ffffff',
  },
  platformBtnActive: {
    backgroundColor: '#4f46e5',
    borderColor: '#4f46e5',
  },
  platformBtnText: {
    fontSize: 11,
    fontWeight: '700',
    color: '#475569',
  },
  platformBtnTextActive: {
    color: '#ffffff',
  },
  submitBtn: {
    backgroundColor: '#4f46e5',
    borderRadius: 8,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 8,
    shadowColor: '#4f46e5',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 2,
  },
  submitBtnDisabled: {
    backgroundColor: '#818cf8',
  },
  submitBtnText: {
    color: '#ffffff',
    fontSize: 15,
    fontWeight: 'bold',
  },
  resultsCard: {
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 16,
    marginTop: 24,
    borderWidth: 1,
    borderColor: '#cbd5e1',
  },
  resultsTitle: {
    fontSize: 14,
    fontWeight: '800',
    color: '#1e293b',
    marginBottom: 4,
  },
  resultsMsg: {
    fontSize: 12,
    color: '#64748b',
    marginBottom: 12,
  },
  resultsList: {
    flexDirection: 'column',
    gap: 10,
  },
  resRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 6,
    borderBottomWidth: 1,
    borderBottomColor: '#f1f5f9',
  },
  resPlatform: {
    fontSize: 12,
    color: '#334155',
    fontWeight: '600',
    flex: 1,
    marginRight: 10,
  },
  resStatusBadge: {
    paddingVertical: 3,
    paddingHorizontal: 8,
    borderRadius: 4,
  },
  queuedBadge: {
    backgroundColor: '#d1fae5',
  },
  dupBadge: {
    backgroundColor: '#f1f5f9',
  },
  resStatusText: {
    fontSize: 10,
    fontWeight: 'bold',
    color: '#475569',
  },
});

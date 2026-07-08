import React, { useState, useEffect, useCallback } from 'react';
import { StyleSheet, View, Text, FlatList, RefreshControl, TouchableOpacity, ActivityIndicator, Alert, Modal, TextInput, ScrollView } from 'react-native';

export default function ProjectsScreen({ userToken, serverUrl }) {
  const [projects, setProjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [modalVisible, setModalVisible] = useState(false);

  // New Project Form state
  const [websiteUrl, setWebsiteUrl] = useState('');
  const [targetKeyword, setTargetKeyword] = useState('');
  const [targetSite, setTargetSite] = useState('');
  const [businessName, setBusinessName] = useState('');
  const [packageType, setPackageType] = useState('basic');
  const [businessDesc, setBusinessDesc] = useState('');
  const [submitting, setSubmitting] = useState(false);

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
      } else {
        Alert.alert('Error', data.error || 'Failed to load projects.');
      }
    } catch (error) {
      console.error(error);
      Alert.alert('Connection Error', 'Could not fetch projects.');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    fetchProjects();
  }, []);

  const onRefresh = useCallback(() => {
    setRefreshing(true);
    fetchProjects();
  }, []);

  const handleAddProject = async () => {
    if (!websiteUrl || !targetKeyword) {
      Alert.alert('Validation Error', 'Website URL and Target Keyword are required.');
      return;
    }

    setSubmitting(true);
    try {
      const response = await fetch(`${serverUrl}/api/projects.php`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${userToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          website_url: websiteUrl.trim(),
          target_keyword: targetKeyword.trim(),
          target_site: targetSite.trim(),
          business_name: businessName.trim(),
          package_type: packageType,
          business_desc: businessDesc.trim(),
        }),
      });

      const data = await response.json();

      if (response.status === 200 && data.success) {
        Alert.alert('Success', 'Project created successfully.');
        setModalVisible(false);
        // Reset form
        setWebsiteUrl('');
        setTargetKeyword('');
        setTargetSite('');
        setBusinessName('');
        setPackageType('basic');
        setBusinessDesc('');
        // Refresh list
        fetchProjects();
      } else {
        Alert.alert('Error', data.error || 'Failed to save project.');
      }
    } catch (error) {
      console.error(error);
      Alert.alert('Connection Error', 'Could not save project to server.');
    } finally {
      setSubmitting(false);
    }
  };

  const renderProjectItem = ({ item }) => (
    <View style={styles.projectCard}>
      <View style={styles.cardHeader}>
        <Text style={styles.projTitle}>{item.business_name || 'Unnamed Campaign'}</Text>
        <View style={styles.badge}>
          <Text style={styles.badgeText}>{item.package_type.toUpperCase()}</Text>
        </View>
      </View>
      
      <Text style={styles.urlText}>🌐 {item.website_url}</Text>
      
      <View style={styles.keywordsContainer}>
        <Text style={styles.keywordLabel}>Keywords:</Text>
        <Text style={styles.keywordVal}>{item.target_keyword}</Text>
      </View>
    </View>
  );

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
          <Text style={styles.headerTitle}>Projects</Text>
          <Text style={styles.headerSub}>Manage SEO Campaigns</Text>
        </View>
        <TouchableOpacity style={styles.addButton} onPress={() => setModalVisible(true)}>
          <Text style={styles.addButtonText}>+ Add New</Text>
        </TouchableOpacity>
      </View>

      <FlatList
        data={projects}
        keyExtractor={(item) => item.id.toString()}
        renderItem={renderProjectItem}
        contentContainerStyle={styles.listContent}
        refreshControl={
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} colors={['#4f46e5']} />
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No projects found. Add one to get started!</Text>
          </View>
        }
      />

      {/* Add Project Modal */}
      <Modal
        animationType="slide"
        transparent={true}
        visible={modalVisible}
        onRequestClose={() => setModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContent}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Add Project</Text>
              <TouchableOpacity onPress={() => setModalVisible(false)}>
                <Text style={styles.closeText}>Cancel</Text>
              </TouchableOpacity>
            </View>

            <ScrollView contentContainerStyle={styles.modalForm}>
              <View style={styles.inputGroup}>
                <Text style={styles.inputLabel}>Business Name</Text>
                <TextInput
                  style={styles.modalInput}
                  placeholder="e.g. My Agency"
                  value={businessName}
                  onChangeText={setBusinessName}
                />
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.inputLabel}>Website URL (Required)</Text>
                <TextInput
                  style={styles.modalInput}
                  placeholder="e.g. https://mywebsite.com"
                  value={websiteUrl}
                  onChangeText={setWebsiteUrl}
                  autoCapitalize="none"
                  autoCorrect={false}
                />
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.inputLabel}>Target Keywords (Required, comma separated)</Text>
                <TextInput
                  style={styles.modalInput}
                  placeholder="e.g. best seo, marketing"
                  value={targetKeyword}
                  onChangeText={setTargetKeyword}
                />
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.inputLabel}>Target URL (Backlink Target, optional)</Text>
                <TextInput
                  style={styles.modalInput}
                  placeholder="e.g. https://mywebsite.com/services"
                  value={targetSite}
                  onChangeText={setTargetSite}
                  autoCapitalize="none"
                  autoCorrect={false}
                />
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.inputLabel}>Package Type</Text>
                <View style={styles.packageSelectors}>
                  {['basic', 'standard', 'premium'].map((pkg) => (
                    <TouchableOpacity
                      key={pkg}
                      style={[styles.pkgBtn, packageType === pkg && styles.pkgBtnActive]}
                      onPress={() => setPackageType(pkg)}
                    >
                      <Text style={[styles.pkgText, packageType === pkg && styles.pkgTextActive]}>
                        {pkg.toUpperCase()}
                      </Text>
                    </TouchableOpacity>
                  ))}
                </View>
              </View>

              <View style={styles.inputGroup}>
                <Text style={styles.inputLabel}>Business Description (Optional)</Text>
                <TextInput
                  style={[styles.modalInput, styles.textArea]}
                  placeholder="Briefly describe what this business does..."
                  value={businessDesc}
                  onChangeText={setBusinessDesc}
                  multiline
                  numberOfLines={4}
                />
              </View>

              <TouchableOpacity
                style={[styles.submitBtn, submitting && styles.submitBtnDisabled]}
                onPress={handleAddProject}
                disabled={submitting}
              >
                <Text style={styles.submitBtnText}>
                  {submitting ? 'Creating Campaign...' : 'Create Project'}
                </Text>
              </TouchableOpacity>
            </ScrollView>
          </View>
        </View>
      </Modal>
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
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
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
  addButton: {
    backgroundColor: '#4f46e5',
    paddingVertical: 8,
    paddingHorizontal: 14,
    borderRadius: 8,
  },
  addButtonText: {
    color: '#ffffff',
    fontSize: 13,
    fontWeight: 'bold',
  },
  listContent: {
    padding: 20,
  },
  projectCard: {
    backgroundColor: '#ffffff',
    borderRadius: 12,
    padding: 16,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#e2e8f0',
    shadowColor: '#0f172a',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.02,
    shadowRadius: 6,
    elevation: 1,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  projTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: '#1e293b',
    flex: 1,
    marginRight: 10,
  },
  badge: {
    backgroundColor: '#e0e7ff',
    paddingVertical: 4,
    paddingHorizontal: 8,
    borderRadius: 6,
  },
  badgeText: {
    color: '#4f46e5',
    fontSize: 10,
    fontWeight: 'bold',
  },
  urlText: {
    fontSize: 13,
    color: '#3b82f6',
    fontWeight: '600',
    marginBottom: 10,
  },
  keywordsContainer: {
    backgroundColor: '#f8fafc',
    padding: 10,
    borderRadius: 6,
  },
  keywordLabel: {
    fontSize: 11,
    fontWeight: 'bold',
    color: '#64748b',
    marginBottom: 2,
  },
  keywordVal: {
    fontSize: 12,
    color: '#334155',
    lineHeight: 18,
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 64,
  },
  emptyText: {
    color: '#64748b',
    fontSize: 14,
    textAlign: 'center',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(15, 23, 42, 0.4)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: '#ffffff',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '90%',
    paddingBottom: 24,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#e2e8f0',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: '#0f172a',
  },
  closeText: {
    color: '#ef4444',
    fontSize: 14,
    fontWeight: 'bold',
  },
  modalForm: {
    padding: 20,
  },
  inputGroup: {
    marginBottom: 16,
  },
  inputLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: '#475569',
    marginBottom: 6,
  },
  modalInput: {
    backgroundColor: '#f8fafc',
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 14,
    color: '#0f172a',
  },
  textArea: {
    height: 80,
    textAlignVertical: 'top',
  },
  packageSelectors: {
    flexDirection: 'row',
    gap: 8,
  },
  pkgBtn: {
    flex: 1,
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 8,
    paddingVertical: 10,
    alignItems: 'center',
    backgroundColor: '#ffffff',
  },
  pkgBtnActive: {
    backgroundColor: '#4f46e5',
    borderColor: '#4f46e5',
  },
  pkgText: {
    fontSize: 11,
    fontWeight: 'bold',
    color: '#475569',
  },
  pkgTextActive: {
    color: '#ffffff',
  },
  submitBtn: {
    backgroundColor: '#4f46e5',
    borderRadius: 8,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 16,
  },
  submitBtnDisabled: {
    backgroundColor: '#818cf8',
  },
  submitBtnText: {
    color: '#ffffff',
    fontSize: 15,
    fontWeight: 'bold',
  },
});
